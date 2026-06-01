<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use App\Models\FormResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationApproved;
use App\Mail\RegistrationRejected;

class RegistrationController extends Controller
{
    /**
     * Register for an event (handles capacity and waitlist).
     * Uses a DB transaction and pessimistic locks to avoid races.
     */
    public function register(Request $request, $eventId)
    {
        $user = $request->user();

        return DB::transaction(function () use ($request, $user, $eventId) {
            $event = Event::lockForUpdate()->find($eventId);
            if (!$event) {
                return response()->json(['message' => 'Event not found'], 404);
            }

            // Prepare questions array (may be filled below)
            $questions = [];

            // Prevent duplicate registrations and reuse cancelled registrations
            $existing = Registration::where('event_id', $event->id)
                ->where('attendee_id', $user->id)
                ->first();

            if ($existing && $existing->status !== 'Cancelled') {
                return response()->json(['message' => 'You have already registered for this event'], 409);
            }

            // Optional additional info validation if event requires it
            $additionalInfo = $request->input('additional_info');
            if ($event->require_additional_info && $event->custom_form_spec) {
                $formSpec = is_string($event->custom_form_spec)
                    ? json_decode($event->custom_form_spec, true)
                    : $event->custom_form_spec;

                $questions = $formSpec['questions'] ?? [];
                $errors = [];
                foreach ($questions as $index => $question) {
                    $isRequired = is_array($question) ? ($question['is_required'] ?? false) : false;
                    $fieldName = "additional_info_{$index}";
                    if ($isRequired && empty($request->input($fieldName))) {
                        $errors[$fieldName] = 'Please answer this required question.';
                    }
                }
                if (!empty($errors)) {
                    return response()->json(['message' => 'Please complete all required fields', 'errors' => $errors], 422);
                }

                // collect answers into associative array if present
                $additionalInfo = [];
                foreach ($questions as $index => $question) {
                    $questionText = is_string($question) ? $question : ($question['question'] ?? $question['name'] ?? "question_{$index}");
                    $fieldName = "additional_info_{$index}";
                    $additionalInfo[$questionText] = $request->input($fieldName);
                }
            }

            // Count confirmed seats
            $confirmedCount = Registration::where('event_id', $event->id)
                ->whereNull('waitlist_position')
                ->whereNotIn('status', ['Cancelled', 'Rejected'])
                ->count();

            if ($confirmedCount < $event->capacity) {
                // AC1: Free events -> start as Pending; Paid events -> auto Confirmed
                $isFree = $event->price === null || (is_numeric($event->price) && floatval($event->price) <= 0);
                $initialStatus = $isFree ? 'Pending' : 'Approved';

                if ($existing && $existing->status === 'Cancelled') {
                    $existing->update([
                        'status' => $initialStatus,
                        'waitlist_position' => null,
                        'additional_info' => $additionalInfo,
                    ]);
                    $registration = $existing;
                } else {
                    $registration = Registration::create([
                        'event_id' => $event->id,
                        'attendee_id' => $user->id,
                        'status' => $initialStatus,
                        'waitlist_position' => null,
                        'additional_info' => $additionalInfo,
                    ]);
                }

                // AC1: Return appropriate success message
                if ($isFree) {
                    // Persist form responses if any
                    if (!empty($questions)) {
                        FormResponse::where('registration_id', $registration->id)->delete();
                        foreach ($questions as $qIndex => $q) {
                            $qText = is_string($q) ? $q : ($q['question'] ?? $q['name'] ?? "question_{$qIndex}");
                            $qType = is_string($q) ? 'text' : ($q['type'] ?? 'text');
                            $val = $additionalInfo[$qText] ?? null;
                            $resp = is_array($val) ? json_encode($val) : ($val !== null ? (string)$val : null);
                            FormResponse::create([
                                'registration_id' => $registration->id,
                                'field_name' => $qText,
                                'field_type' => $qType,
                                'response_value' => $resp,
                            ]);
                        }
                    }

                    return response()->json(['message' => 'Registration request submitted successfully. Please wait for approval.', 'data' => $registration], 201);
                }

                // Persist form responses if any
                if (!empty($questions)) {
                    FormResponse::where('registration_id', $registration->id)->delete();
                    foreach ($questions as $qIndex => $q) {
                        $qText = is_string($q) ? $q : ($q['question'] ?? $q['name'] ?? "question_{$qIndex}");
                        $qType = is_string($q) ? 'text' : ($q['type'] ?? 'text');
                        $val = $additionalInfo[$qText] ?? null;
                        $resp = is_array($val) ? json_encode($val) : ($val !== null ? (string)$val : null);
                        FormResponse::create([
                            'registration_id' => $registration->id,
                            'field_name' => $qText,
                            'field_type' => $qType,
                            'response_value' => $resp,
                        ]);
                    }
                }

                return response()->json(['message' => 'Registration completed successfully!', 'data' => $registration], 201);
            }

            // Event full -> join waitlist
            $nextPosition = Registration::where('event_id', $event->id)
                ->whereNotNull('waitlist_position')
                ->max('waitlist_position');
            $nextPosition = ($nextPosition ?? 0) + 1;

            if ($existing && $existing->status === 'Cancelled') {
                $existing->update([
                    'status' => 'Waitlisted',
                    'waitlist_position' => $nextPosition,
                    'additional_info' => $additionalInfo,
                ]);
                $registration = $existing;
            } else {
                $registration = Registration::create([
                    'event_id' => $event->id,
                    'attendee_id' => $user->id,
                    'status' => 'Waitlisted',
                    'waitlist_position' => $nextPosition,
                    'additional_info' => $additionalInfo,
                ]);
            }

            // Persist form responses if any
            if (!empty($questions)) {
                FormResponse::where('registration_id', $registration->id)->delete();
                foreach ($questions as $qIndex => $q) {
                    $qText = is_string($q) ? $q : ($q['question'] ?? $q['name'] ?? "question_{$qIndex}");
                    $qType = is_string($q) ? 'text' : ($q['type'] ?? 'text');
                    $val = $additionalInfo[$qText] ?? null;
                    $resp = is_array($val) ? json_encode($val) : ($val !== null ? (string)$val : null);
                    FormResponse::create([
                        'registration_id' => $registration->id,
                        'field_name' => $qText,
                        'field_type' => $qType,
                        'response_value' => $resp,
                    ]);
                }
            }

            return response()->json([
                'message' => 'Event is fully booked. You have been added to the waitlist!',
                'waitlist_position' => $nextPosition,
                'data' => $registration,
            ], 201);
        });
    }

    /**
     * Cancel a registration (user action). Promotes first waitlist if a confirmed seat freed.
     */
    public function cancelRegistration(Request $request, $registrationId)
    {
        $user = $request->user();

        return DB::transaction(function () use ($user, $registrationId) {
            $registration = Registration::lockForUpdate()
                ->where('id', $registrationId)
                ->where('attendee_id', $user->id)
                ->first();

            if (!$registration) {
                return response()->json(['message' => 'Registration not found'], 404);
            }

            if ($registration->status === 'Cancelled') {
                return response()->json(['message' => 'Registration already cancelled'], 400);
            }

            $event = $registration->event;
            if ($event) {
                // Paid events cannot be cancelled by attendee
                $isPaid = $event->price !== null && is_numeric($event->price) && floatval($event->price) > 0;
                if ($isPaid) {
                    return response()->json(['message' => 'Paid events cannot be cancelled'], 400);
                }

                // Cannot cancel after event has started
                if ($event->date_time && now()->greaterThanOrEqualTo($event->date_time)) {
                    return response()->json(['message' => 'Cannot cancel after event has started'], 400);
                }
            }

            $wasConfirmed = $registration->waitlist_position === null && $registration->status !== 'Waitlisted';

            $registration->update(['status' => 'Cancelled', 'waitlist_position' => null]);

            if ($wasConfirmed) {
                $this->promoteFromWaitlist($registration->event_id);
            }

            return response()->json(['message' => 'Registration cancelled successfully', 'data' => $registration], 200);
        });
    }

    /**
     * Promote first waitlisted attendee to confirmed and shift positions.
     */
    private function promoteFromWaitlist(int $eventId): void
    {
        $first = Registration::where('event_id', $eventId)
            ->where('status', 'Waitlisted')
            ->whereNotNull('waitlist_position')
            ->orderBy('waitlist_position', 'asc')
            ->lockForUpdate()
            ->first();

        if (!$first) {
            return;
        }

        $first->update(['status' => 'Approved', 'waitlist_position' => null]);

        $event = Event::find($eventId);
        $eventName = $event ? $event->name : 'Event';
        $eventTime = $event && $event->date_time ? $event->date_time->format('d/m/Y H:i') : null;

        // Create notification if model exists
        if (class_exists('\App\\Models\\Notification')) {
            $message = "You have been moved from the waitlist to confirmed for this event";
            if ($eventName) $message .= " - {$eventName}";
            if ($eventTime) $message .= " at {$eventTime}";

            \App\Models\Notification::create([
                'user_id' => $first->attendee_id,
                'event_id' => $eventId,
                'message' => $message,
                'is_read' => false,
            ]);
        }

        // Send approval email for promoted attendee
        if ($first->attendee && $first->attendee->email) {
            Mail::to($first->attendee->email)->send(new RegistrationApproved($first));
        }

        // Shift remaining waitlist positions down by 1
        Registration::where('event_id', $eventId)
            ->where('status', 'Waitlisted')
            ->whereNotNull('waitlist_position')
            ->decrement('waitlist_position');
    }

    /**
     * Organizer: list participants for an event including waitlist positions.
     */
    public function eventParticipants(Request $request, $eventId)
    {
        $user = $request->user();
        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }
        if ($event->organizer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden: Only the organizer can view participants'], 403);
        }

        $registrations = Registration::with(['attendee', 'formResponses'])
            ->where('event_id', $eventId)
            ->orderByRaw("CASE WHEN status = 'Waitlisted' THEN 1 ELSE 0 END")
            ->orderBy('waitlist_position', 'asc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($reg) {
                return [
                    'id' => $reg->id,
                    'status' => $reg->status,
                    'waitlist_position' => $reg->waitlist_position,
                    'registered_at' => $reg->created_at?->format('d/m/Y H:i'),
                    'attendee' => $reg->attendee ? [
                        'id' => $reg->attendee->id,
                        'name' => $reg->attendee->name,
                        'email' => $reg->attendee->email,
                        'avatar' => $reg->attendee->avatar ?? null,
                    ] : null,
                    'form_responses' => $reg->formResponses->map(fn($fr) => [
                        'field_name' => $fr->field_name,
                        'field_type' => $fr->field_type,
                        'response_value' => $fr->response_value,
                    ]),
                ];
            });

        return response()->json(['data' => $registrations, 'event' => ['id' => $event->id, 'name' => $event->name, 'capacity' => $event->capacity]], 200);
    }

    /**
     * Organizer: Approve a registration. Sends queued email notification.
     */
    public function organizerApprove(Request $request, $registrationId)
    {
        $user = $request->user();

        return DB::transaction(function () use ($user, $registrationId) {
            $registration = Registration::lockForUpdate()->with('event', 'attendee')->find($registrationId);
            if (!$registration) return response()->json(['message' => 'Registration not found'], 404);

            $event = $registration->event;
            if (!$event || $event->organizer_id !== $user->id) {
                return response()->json(['message' => 'Forbidden: Only the organizer can perform this action'], 403);
            }

            if ($registration->status === 'Approved') {
                return response()->json(['message' => 'Registration already approved'], 400);
            }

            // Count confirmed seats, excluding the current pending registration if it is already occupying a reserved slot.
            $confirmedCount = Registration::where('event_id', $event->id)
                ->whereNull('waitlist_position')
                ->whereNotIn('status', ['Cancelled', 'Rejected'])
                ->when(
                    $registration->status === 'Pending' && $registration->waitlist_position === null,
                    fn($query) => $query->where('id', '!=', $registration->id)
                )
                ->count();

            if ($confirmedCount >= $event->capacity) {
                return response()->json(['message' => 'Event capacity reached; cannot approve'], 400);
            }

            $wasWaitlisted = $registration->status === 'Waitlisted' && $registration->waitlist_position !== null;
            $oldWaitPos = $registration->waitlist_position;

            $registration->update(['status' => 'Approved', 'waitlist_position' => null]);

            // If the user was waitlisted, shift others down only for positions greater than the old one
            if ($wasWaitlisted && $oldWaitPos !== null) {
                Registration::where('event_id', $event->id)
                    ->where('status', 'Waitlisted')
                    ->whereNotNull('waitlist_position')
                    ->where('waitlist_position', '>', $oldWaitPos)
                    ->decrement('waitlist_position');
            }

            // Create notification record if model exists
            if (class_exists('\App\\Models\\Notification')) {
                // If user was waitlisted and is now approved, use the specific promotion message per AC
                if ($wasWaitlisted) {
                    $eventTime = $event && $event->date_time ? $event->date_time->format('d/m/Y H:i') : null;
                    $message = "You have been moved from the waitlist to confirmed for this event";
                    if ($event->name) $message .= " - {$event->name}";
                    if ($eventTime) $message .= " at {$eventTime}";
                } else {
                    $message = "Your registration for the event '{$event->name}' has been approved.";
                }

                \App\Models\Notification::create([
                    'user_id' => $registration->attendee_id,
                    'event_id' => $event->id,
                    'message' => $message,
                    'is_read' => false,
                ]);
            }

            // Send approval email
            if ($registration->attendee && $registration->attendee->email) {
                Mail::to($registration->attendee->email)->send(new RegistrationApproved($registration));
            }

            return response()->json(['message' => 'Registration approved', 'data' => $registration], 200);
        });
    }

    /**
     * Compatibility wrapper for route naming: approveRegistration
     */
    public function approveRegistration(Request $request, $registrationId)
    {
        return $this->organizerApprove($request, $registrationId);
    }

    /**
     * Organizer: Reject a registration. Sends queued email and promotes waitlist if needed.
     */
    public function organizerReject(Request $request, $registrationId)
    {
        $user = $request->user();

        return DB::transaction(function () use ($user, $registrationId) {
            $registration = Registration::lockForUpdate()->with('event', 'attendee')->find($registrationId);
            if (!$registration) return response()->json(['message' => 'Registration not found'], 404);

            $event = $registration->event;
            if (!$event || $event->organizer_id !== $user->id) {
                return response()->json(['message' => 'Forbidden: Only the organizer can perform this action'], 403);
            }

            if ($registration->status === 'Rejected') {
                return response()->json(['message' => 'Registration already rejected'], 400);
            }

            $wasConfirmed = $registration->waitlist_position === null && $registration->status !== 'Waitlisted';

            // Clear waitlist position and mark rejected
            $oldWaitPos = $registration->waitlist_position;
            $registration->update(['status' => 'Rejected', 'waitlist_position' => null]);

            // If rejected from confirmed seats, promote first waitlist
            if ($wasConfirmed) {
                $this->promoteFromWaitlist($event->id);
            }

            // If rejected from waitlist, shift down positions
            if ($oldWaitPos !== null) {
                Registration::where('event_id', $event->id)
                    ->where('status', 'Waitlisted')
                    ->whereNotNull('waitlist_position')
                    ->where('waitlist_position', '>', $oldWaitPos)
                    ->decrement('waitlist_position');
            }

            // Notification
            if (class_exists('\App\\Models\\Notification')) {
                \App\Models\Notification::create([
                    'user_id' => $registration->attendee_id,
                    'event_id' => $event->id,
                    'message' => "Your registration for the event '{$event->name}' was not approved.",
                    'is_read' => false,
                ]);
            }

            // Send rejection email
            if ($registration->attendee && $registration->attendee->email) {
                Mail::to($registration->attendee->email)->send(new RegistrationRejected($registration));
            }

            return response()->json(['message' => 'Registration rejected', 'data' => $registration], 200);
        });
    }

    /**
     * Compatibility wrapper for route naming: rejectRegistration
     */
    public function rejectRegistration(Request $request, $registrationId)
    {
        return $this->organizerReject($request, $registrationId);
    }

    /**
     * Organizer: list all participants across organizer's events (filterable).
     */
    public function allParticipants(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'organizer') {
            return response()->json(['message' => 'Forbidden: Only organizers can view participants'], 403);
        }

        $organizerEventIds = Event::where('organizer_id', $user->id)->pluck('id');

        $query = Registration::with(['attendee', 'event', 'formResponses'])
            ->whereIn('event_id', $organizerEventIds);

        if ($request->has('event_id') && $request->event_id !== 'all') {
            $query->where('event_id', $request->event_id);
        }
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $registrations = $query->orderBy('created_at', 'desc')->get()
            ->map(function ($reg) {
                return [
                    'id' => $reg->id,
                    'status' => $reg->status,
                    'waitlist_position' => $reg->waitlist_position,
                    'registered_at' => $reg->created_at?->format('d/m/Y H:i'),
                    'event' => $reg->event ? [
                        'id' => $reg->event->id,
                        'name' => $reg->event->name,
                    ] : null,
                    'attendee' => $reg->attendee ? [
                        'id' => $reg->attendee->id,
                        'name' => $reg->attendee->name,
                        'email' => $reg->attendee->email,
                        'avatar' => $reg->attendee->avatar ?? null,
                    ] : null,
                    'form_responses' => $reg->formResponses->map(fn($fr) => [
                        'field_name' => $fr->field_name,
                        'field_type' => $fr->field_type,
                        'response_value' => $fr->response_value,
                    ]),
                    'additional_info' => $reg->additional_info ?? null,
                    'payment_id' => $reg->payment_id,
                ];
            });

        return response()->json(['data' => $registrations], 200);
    }

    /**
     * Get user's registrations
     */
    public function getMyRegistrations(Request $request)
    {
        $user = $request->user();
        $registrations = Registration::with(['event.category'])
            ->where('attendee_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $registrations], 200);
    }

    /**
     * Check if user has registered for an event
     */
    public function checkRegistration(Request $request, $eventId)
    {
        $user = $request->user();
        $registration = Registration::where('event_id', $eventId)
            ->where('attendee_id', $user->id)
            ->whereIn('status', ['Pending', 'Approved', 'Waitlisted'])
            ->first();

        if ($registration) {
            return response()->json(['has_registered' => true, 'status' => $registration->status, 'registration_id' => $registration->id], 200);
        }
        return response()->json(['has_registered' => false], 200);
    }

    /**
     * Get notifications for current user (if Notification model exists).
     */
    public function getNotifications(Request $request)
    {
        $user = $request->user();
        if (!class_exists('\App\\Models\\Notification')) {
            return response()->json(['success' => true, 'data' => []], 200);
        }

        $notifications = \App\Models\Notification::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'data' => $notifications], 200);
    }

    /**
     * Mark a notification as read for current user
     */
    public function markNotificationRead(Request $request, $id)
    {
        $user = $request->user();
        if (!class_exists('\App\\Models\\Notification')) {
            return response()->json(['success' => false, 'message' => 'Not supported'], 400);
        }

        $notification = \App\Models\Notification::where('id', $id)->where('user_id', $user->id)->first();
        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        $notification->is_read = true;
        $notification->save();

        return response()->json(['success' => true, 'data' => $notification], 200);
    }

    /**
     * Get user's profile with registrations grouped by status
     */
    public function getProfileWithRegistrations(Request $request)
    {
        $user = $request->user();
        $registrations = Registration::with('event.category')
            ->where('attendee_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['user' => $user, 'registrations' => $registrations], 200);
    }
}
