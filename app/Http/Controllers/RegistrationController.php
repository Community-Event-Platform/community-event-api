<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * RegistrationController
 *
 * Handles all registration-related logic including:
 * - CEP-83: Add attendee to waitlist when event is full
 * - CEP-84: Return waitlist position in response
 * - CEP-85: Automatic promotion from waitlist on cancellation
 * - CEP-86: Update registration status after promotion
 * - CEP-87: Handle concurrent registration via DB transactions + locks
 */
class RegistrationController extends Controller
{
    /**
     * CEP-83 / CEP-87: Register for an event or join the waitlist if full.
     * Uses a DB transaction with a pessimistic lock (lockForUpdate) to safely
     * handle concurrent registrations without double-booking.
     */
    public function register(Request $request, $id)
    {
        $user = $request->user();

        return DB::transaction(function () use ($user, $id) {
            // Lock the event row to prevent concurrent race conditions (CEP-87)
            $event = Event::lockForUpdate()->find($id);

            if (!$event) {
                return response()->json(['message' => 'Event not found'], 404);
            }

            // Check if the user already has a registration (any status except Cancelled)
            $existing = Registration::where('event_id', $event->id)
                ->where('attendee_id', $user->id)
                ->whereNotIn('status', ['Cancelled'])
                ->first();

            if ($existing) {
                if ($existing->waitlist_position !== null) {
                    return response()->json([
                        'message' => 'Bạn đang ở trong danh sách chờ!',
                        'waitlist_position' => $existing->waitlist_position,
                    ], 400);
                }
                return response()->json(['message' => 'Bạn đã đăng ký tham gia sự kiện này rồi!'], 400);
            }

            // Count CONFIRMED seats (not waitlisted, not cancelled)
            $confirmedCount = Registration::where('event_id', $event->id)
                ->confirmed()
                ->count();

            if ($confirmedCount < $event->capacity) {
                // CEP-83: Seat available — register normally
                $registration = Registration::create([
                    'event_id'         => $event->id,
                    'attendee_id'      => $user->id,
                    'status'           => 'Approved',
                    'waitlist_position' => null,
                ]);

                return response()->json([
                    'message' => 'Đăng ký tham gia thành công!',
                    'data'    => $registration,
                ], 201);
            }

            // CEP-83: Event is full — add to waitlist (FIFO)
            $nextPosition = Registration::where('event_id', $event->id)
                ->whereNotNull('waitlist_position')
                ->max('waitlist_position');

            $nextPosition = ($nextPosition ?? 0) + 1;

            $registration = Registration::create([
                'event_id'          => $event->id,
                'attendee_id'       => $user->id,
                'status'            => 'Waitlisted',
                'waitlist_position' => $nextPosition, // CEP-84: track position
            ]);

            return response()->json([
                'message'          => 'Sự kiện đã hết ghế. Bạn đã được thêm vào danh sách chờ!',
                'waitlist_position' => $nextPosition,
                'data'             => $registration,
            ], 201);
        });
    }

    /**
     * CEP-85 / CEP-86: Cancel a registration and automatically promote the
     * first person in the waitlist (FIFO). Wrapped in a DB transaction to
     * ensure atomicity and prevent concurrent promotion issues (CEP-87).
     */
    public function cancel(Request $request, $registrationId)
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

            if (in_array($registration->status, ['Cancelled'])) {
                return response()->json(['message' => 'Registration is already cancelled'], 400);
            }

            $wasConfirmed = $registration->waitlist_position === null
                         && !in_array($registration->status, ['Waitlisted']);

            // Mark this registration as cancelled
            $registration->update([
                'status'            => 'Cancelled',
                'waitlist_position' => null,
            ]);

            // CEP-85/86: If a confirmed seat was freed, promote the next person in the waitlist
            if ($wasConfirmed) {
                $this->promoteFromWaitlist($registration->event_id);
            }

            return response()->json(['message' => 'Đã huỷ đăng ký thành công!'], 200);
        });
    }

    /**
     * CEP-85/86: Promote the first person in the waitlist to a confirmed seat.
     * Then shift all remaining waitlist positions down by 1 (FIFO ordering).
     *
     * @param int $eventId
     */
    private function promoteFromWaitlist(int $eventId): void
    {
        // 1. Tìm người đứng đầu hàng đợi (waitlist_position = 1)
        $firstInLine = Registration::where('event_id', $eventId)
            ->where('status', 'Waitlisted')
            ->where('waitlist_position', 1)
            ->lockForUpdate() // Khóa dòng dữ liệu để tránh xung đột dữ liệu (Race Condition)
            ->first();

        // Nếu không có ai trong danh sách chờ thì dừng lại
        if (!$firstInLine) {
            return;
        }

        // 2. Đôn người này lên làm thành viên chính thức
        $firstInLine->update([
            'status'            => 'Approved',
            'waitlist_position' => null, // Được tham gia rồi thì không còn số chờ nữa
        ]);

        // 3. Lấy tên sự kiện để nội dung thông báo rõ ràng hơn
        $event = Event::find($eventId);
        $eventName = $event ? $event->name : 'Sự kiện';

        // 4. BẮN THÔNG BÁO (Lưu vào bảng notifications 
        \App\Models\Notification::create([
            'user_id'  => $firstInLine->attendee_id, // ID của người được đôn lên
            'event_id' => $eventId,
            'message'  => "Congratulations! You have been promoted to a permanent position for the event \"{$eventName}\".", // Nội dung thông báo
            'is_read'  => false,
        ]);

        // 5. Cập nhật lại số thứ tự cho toàn bộ những người còn lại trong danh sách chờ (Trừ đi 1)
        Registration::where('event_id', $eventId)
            ->where('status', 'Waitlisted')
            ->whereNotNull('waitlist_position')
            ->decrement('waitlist_position');
    }

    /**
     * Get all registrations for a specific event (organizer only).
     * Used by the Participants page on the frontend.
     * CEP-84: Returns waitlist_position for each participant.
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
                    'id'                => $reg->id,
                    'status'            => $reg->status,
                    'waitlist_position' => $reg->waitlist_position,
                    'registered_at'     => $reg->created_at->format('d/m/Y H:i'),
                    'attendee' => [
                        'id'     => $reg->attendee->id,
                        'name'   => $reg->attendee->name,
                        'email'  => $reg->attendee->email,
                        'avatar' => $reg->attendee->avatar ?? null,
                    ],
                    'form_responses' => $reg->formResponses->map(fn($fr) => [
                        'field_name'     => $fr->field_name,
                        'field_type'     => $fr->field_type,
                        'response_value' => $fr->response_value,
                    ]),
                ];
            });

        return response()->json([
            'data'  => $registrations,
            'event' => [
                'id'       => $event->id,
                'name'     => $event->name,
                'capacity' => $event->capacity,
            ],
        ], 200);
    }

    /**
     * Get all participants across all events for an organizer.
     * Supports filtering by event_id and status.
     */
    public function allParticipants(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'organizer') {
            return response()->json(['message' => 'Forbidden: Only organizers can view participants'], 403);
        }

        // Get IDs of events belonging to this organizer
        $organizerEventIds = Event::where('organizer_id', $user->id)->pluck('id');

        $query = Registration::with(['attendee', 'event', 'formResponses'])
            ->whereIn('event_id', $organizerEventIds);

        // Filter by event
        if ($request->has('event_id') && $request->event_id !== 'all') {
            $query->where('event_id', $request->event_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $registrations = $query
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reg) {
                return [
                    'id'                => $reg->id,
                    'status'            => $reg->status,
                    'waitlist_position' => $reg->waitlist_position,
                    'registered_at'     => $reg->created_at->format('d/m/Y H:i'),
                    'attendee' => [
                        'id'     => $reg->attendee->id ?? null,
                        'name'   => $reg->attendee->name ?? 'Unknown',
                        'email'  => $reg->attendee->email ?? '',
                        'avatar' => $reg->attendee->avatar ?? null,
                    ],
                    'event' => [
                        'id'        => $reg->event->id ?? null,
                        'name'      => $reg->event->name ?? 'Unknown',
                        'date_time' => $reg->event->date_time ?? null,
                    ],
                    'has_form_responses' => $reg->formResponses->isNotEmpty(),
                    'form_responses'     => $reg->formResponses->map(fn($fr) => [
                        'field_name'     => $fr->field_name,
                        'field_type'     => $fr->field_type,
                        'response_value' => $fr->response_value,
                    ]),
                ];
            });

        return response()->json(['data' => $registrations], 200);
    }
}
