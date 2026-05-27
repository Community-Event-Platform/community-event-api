<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

            // Prevent duplicate registrations (except Cancelled)
            $existing = Registration::where('event_id', $event->id)
                ->where('attendee_id', $user->id)
                ->whereNotIn('status', ['Cancelled'])
                ->first();

            if ($existing) {
                return response()->json(['message' => 'Bạn đã đăng ký tham gia sự kiện này rồi!'], 400);
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
                        $errors[$fieldName] = 'Vui lòng trả lời câu hỏi bắt buộc.';
                    }
                }
                if (!empty($errors)) {
                    return response()->json(['message' => 'Vui lòng điền đầy đủ thông tin bắt buộc', 'errors' => $errors], 422);
                }

                // collect answers into associative array if present
                $additionalInfo = [];
                foreach ($questions as $index => $question) {
                    $questionText = is_string($question) ? $question : ($question['question'] ?? "question_{$index}");
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
                $registration = Registration::create([
                    'event_id' => $event->id,
                    'attendee_id' => $user->id,
                    'status' => 'Approved',
                    'waitlist_position' => null,
                    'additional_info' => $additionalInfo,
                ]);

                return response()->json(['message' => 'Đăng ký tham gia thành công!', 'data' => $registration], 201);
            }

            // Event full -> join waitlist
            $nextPosition = Registration::where('event_id', $event->id)
                ->whereNotNull('waitlist_position')
                ->max('waitlist_position');
            $nextPosition = ($nextPosition ?? 0) + 1;

            $registration = Registration::create([
                'event_id' => $event->id,
                'attendee_id' => $user->id,
                'status' => 'Waitlisted',
                'waitlist_position' => $nextPosition,
                'additional_info' => $additionalInfo,
            ]);

            return response()->json([
                'message' => 'Sự kiện đã hết ghế. Bạn đã được thêm vào danh sách chờ!',
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

            $wasConfirmed = $registration->waitlist_position === null && $registration->status !== 'Waitlisted';

            $registration->update(['status' => 'Cancelled', 'waitlist_position' => null]);

            if ($wasConfirmed) {
                $this->promoteFromWaitlist($registration->event_id);
            }

            return response()->json(['message' => 'Hủy đăng ký thành công', 'data' => $registration], 200);
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
        $eventName = $event ? $event->name : 'Sự kiện';

        // Create notification if model exists
        if (class_exists('\App\\Models\\Notification')) {
            \App\Models\Notification::create([
                'user_id' => $first->attendee_id,
                'event_id' => $eventId,
                'message' => "Bạn đã được đôn lên tham gia sự kiện \"{$eventName}\".",
                'is_read' => false,
            ]);
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

        $registrations = $query->orderBy('created_at', 'desc')->get();

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
