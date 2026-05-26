<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegistrationController extends Controller
{
    /**
     * Register for a Free Event (with optional requirement form)
     */
    public function registerFreeEvent(Request $request, $eventId)
    {
        $user = $request->user();
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // AC3: Check for duplicate registration (Pending or Confirmed status)
        $existingRegistration = Registration::where('event_id', $event->id)
            ->where('attendee_id', $user->id)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->first();

        if ($existingRegistration) {
            return response()->json([
                'message' => 'Bạn đã đăng ký sự kiện này',
                'code' => 'DUPLICATE_REGISTRATION'
            ], 409);
        }

        // Check capacity - calculate remaining seats from registrations count
        $registrationsCount = Registration::where('event_id', $event->id)->count();
        $remainingSeats = max(0, $event->capacity - $registrationsCount);

        if ($remainingSeats <= 0) {
            return response()->json(['message' => 'Sự kiện đã hết ghế trống!'], 400);
        }

        // AC2: Validate required fields if event has requirement form
        $additionalInfo = null;
        if ($event->require_additional_info && $event->custom_form_spec) {
            $formSpec = is_string($event->custom_form_spec) 
                ? json_decode($event->custom_form_spec, true) 
                : $event->custom_form_spec;
            
            $questions = $formSpec['questions'] ?? [];
            $errors = [];
            
            foreach ($questions as $index => $question) {
                // Handle both string questions and object questions
                $questionText = is_string($question) ? $question : ($question['question'] ?? '');
                $isRequired = is_array($question) ? ($question['is_required'] ?? false) : false;
                
                if ($isRequired) {
                    $fieldName = "additional_info_{$index}";
                    if (empty($request->input($fieldName))) {
                        $errors[$fieldName] = "Vui lòng trả lời: " . $questionText;
                    }
                }
            }
            
            if (!empty($errors)) {
                return response()->json([
                    'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc',
                    'errors' => $errors
                ], 422);
            }
            
            // Collect additional info
            $additionalInfo = [];
            foreach ($questions as $index => $question) {
                $questionText = is_string($question) ? $question : ($question['question'] ?? '');
                $fieldName = "additional_info_{$index}";
                $additionalInfo[$questionText] = $request->input($fieldName);
            }
        }

        // AC1: Create registration with status 'Pending'
        $registration = Registration::create([
            'event_id' => $event->id,
            'attendee_id' => $user->id,
            'status' => 'Pending',
            'additional_info' => $additionalInfo,
        ]);

        return response()->json([
            'message' => 'Gửi yêu cầu đăng ký thành công, vui lòng chờ duyệt!',
            'data' => $registration
        ], 201);
    }

    /**
     * Register for a Paid Event
     */
    public function registerPaidEvent(Request $request, $eventId)
    {
        $user = $request->user();
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // AC3: Check for duplicate registration (Pending or Confirmed status)
        $existingRegistration = Registration::where('event_id', $event->id)
            ->where('attendee_id', $user->id)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->first();

        if ($existingRegistration) {
            return response()->json([
                'message' => 'Bạn đã đăng ký sự kiện này',
                'code' => 'DUPLICATE_REGISTRATION'
            ], 409);
        }

        // Check capacity - calculate remaining seats from registrations count
        $registrationsCount = Registration::where('event_id', $event->id)->count();
        $remainingSeats = max(0, $event->capacity - $registrationsCount);

        if ($remainingSeats <= 0) {
            return response()->json(['message' => 'Sự kiện đã hết ghế trống!'], 400);
        }

        // Validate quantity - use numeric check for remaining seats
        $maxQuantity = is_numeric($remainingSeats) ? $remainingSeats : 1;
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:' . $maxQuantity,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $quantity = $request->input('quantity', 1);

        // AC4: For paid events, status is set based on payment result
        // For now, we set to 'Pending' since payment integration is not implemented
        // In production, this would be 'Confirmed' after successful payment
        $registration = Registration::create([
            'event_id' => $event->id,
            'attendee_id' => $user->id,
            'status' => 'Pending',
            'additional_info' => [
                'quantity' => $quantity,
                'payment_method' => $request->input('payment_method', 'credit_card'),
            ],
        ]);

        // AC4: No success message returned - FE should redirect to payment
        $amount = is_numeric($event->price) ? $event->price * $quantity : 0;
        return response()->json([
            'message' => 'Proceeding to payment...',
            'data' => $registration,
            'requires_payment' => true,
            'amount' => $amount
        ], 201);
    }

    /**
     * Get user's registrations
     */
    public function getMyRegistrations(Request $request)
    {
        $user = $request->user();
        
        $registrations = Registration::with('event')
            ->where('attendee_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $registrations], 200);
    }

    /**
     * Cancel registration
     */
    public function cancelRegistration(Request $request, $registrationId)
    {
        $user = $request->user();
        
        $registration = Registration::where('id', $registrationId)
            ->where('attendee_id', $user->id)
            ->first();

        if (!$registration) {
            return response()->json(['message' => 'Registration not found'], 404);
        }

        if ($registration->status === 'Cancelled') {
            return response()->json(['message' => 'Registration already cancelled'], 400);
        }

        $registration->update(['status' => 'Cancelled']);

        return response()->json([
            'message' => 'Hủy đăng ký thành công',
            'data' => $registration
        ], 200);
    }

    /**
     * Check if user has registered for an event
     */
    public function checkRegistration(Request $request, $eventId)
    {
        $user = $request->user();
        
        $registration = Registration::where('event_id', $eventId)
            ->where('attendee_id', $user->id)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->first();

        if ($registration) {
            return response()->json([
                'has_registered' => true,
                'status' => $registration->status,
                'registration_id' => $registration->id,
            ], 200);
        }

        return response()->json([
            'has_registered' => false,
        ], 200);
    }
}
