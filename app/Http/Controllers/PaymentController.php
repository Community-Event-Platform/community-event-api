<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Initialize payment for a paid event registration.
     *
     * POST /api/events/{eventId}/register/paid
     */
    public function initPayment(Request $request, $eventId)
    {
        $user = $request->user();

        // Validate request
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'payment_method' => 'required|string|in:vnpay,paypal,stripe,credit_card',
        ]);

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        if ($event->is_free) {
            return response()->json(['message' => 'This event is free, use /events/{id}/register endpoint'], 400);
        }

        // Calculate total amount
        $quantity = $validated['quantity'];
        $totalAmount = $event->price * $quantity;
        $fee = $event->price * $quantity * 0.02; // 2% platform fee
        $finalAmount = $totalAmount + $fee;

        // Create payment record
        $payment = Payment::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'payment_method' => $validated['payment_method'],
            'amount' => $finalAmount,
            'currency' => 'VND',
            'status' => Payment::STATUS_PENDING,
        ]);

        // Generate payment URL based on method
        $paymentUrl = $this->generatePaymentUrl($payment, $event, $quantity);

        if (!$paymentUrl) {
            $payment->update(['status' => Payment::STATUS_FAILED]);
            return response()->json(['message' => 'Failed to initialize payment'], 500);
        }

        // If integration is not implemented and returns a placeholder URL,
        // treat it as an immediate completed payment for local/testing flow.
        if (str_starts_with($paymentUrl, '#')) {
            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'paid_at' => now(),
                'transaction_id' => $payment->transaction_id ?? "direct_{$payment->id}_" . time(),
            ]);

            $this->createRegistrationFromPayment($payment);
            $payment->refresh();

            return response()->json([
                'message' => 'Payment completed and registration confirmed.',
                'payment_id' => $payment->id,
                'payment_status' => $payment->status,
                'registration' => $payment->registration,
                'amount' => $finalAmount,
                'currency' => 'VND',
            ]);
        }

        $payment->update(['payment_url' => $paymentUrl]);

        return response()->json([
            'message' => 'Payment initialized',
            'payment_id' => $payment->id,
            'payment_url' => $paymentUrl,
            'amount' => $finalAmount,
            'currency' => 'VND',
        ]);
    }

    /**
     * VNPay return URL - called after payment on VNPay side.
     *
     * GET /api/payment/vnpay/return
     */
    public function vnpayReturn(Request $request)
    {
        $vnpayData = $request->all();

        Log::info('VNPay Return:', $vnpayData);

        // Verify signature
        if (!$this->verifyVnpaySignature($vnpayData)) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $vnpayTxnRef = $vnpayData['vnp_txn_ref'] ?? null;
        $vnpayResponseCode = $vnpayData['vnp_response_code'] ?? '99';

        // Find payment by transaction reference
        $payment = Payment::where('transaction_id', $vnpayTxnRef)->first();

        if (!$payment) {
            // Try payment ID if transaction ID format different
            $paymentId = $vnpeyData['vnp_txn_ref'] ?? null;
            if ($paymentId) {
                $payment = Payment::find($paymentId);
            }
        }

        if (!$payment) {
            Log::error('Payment not found forVNPay return:', $vnpayData);
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Process based on response code
        if ($vnpayResponseCode === '00') {
            // Success
            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'transaction_id' => $vnpayData['vnp_transaction_no'] ?? $vnpayTxnRef,
                'paid_at' => now(),
                'payment_data' => $vnpayData,
            ]);

            // Auto-create registration after successful payment
            $this->createRegistrationFromPayment($payment);

            return response()->json([
                'message' => 'Thanh toán thành công!',
                'payment_status' => 'completed',
                'registration' => $payment->registration,
            ]);
        } else {
            // Failed
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'payment_data' => $vnpayData,
            ]);

            $errorMessages = [
                '07' => 'Order has been confirmed',
                '09' => 'Card not registered',
                '10' => 'Invalid card',
                '11' => 'Expired card',
                '24' => 'Card closed',
                '99' => 'Other errors',
            ];

            $errorMsg = $errorMessages[$vnpayResponseCode] ?? 'Thanh toán thất bại';

            return response()->json([
                'message' => $errorMsg,
                'payment_status' => 'failed',
                'error_code' => $vnpayResponseCode,
            ], 400);
        }
    }

    /**
     * VNPay IPN (Instant Payment Notification) -后台回调.
     *
     * POST /api/payment/vnpay/ipn
     */
    public function vnpayIpn(Request $request)
    {
        $vnpayData = $request->all();

        Log::info('VNPay IPN:', $vnpayData);

        if (!$this->verifyVnpaySignature($vnpayData)) {
            return response()->response('')->setStatusCode(400)->send();
        }

        $vnpayTxnRef = $vnpayData['vnp_txn_ref'] ?? null;
        $vnpayResponseCode = $vnpayData['vnp_response_code'] ?? '99';

        $payment = Payment::where('transaction_id', $vnpayTxnRef)->first();

        if (!$payment) {
            Log::error('IPN: Payment not found', ['txn_ref' => $vnpayTxnRef]);
            return response('')->setStatusCode(404)->send();
        }

        if ($vnpayResponseCode === '00' && $payment->status !== Payment::STATUS_COMPLETED) {
            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'paid_at' => now(),
                'payment_data' => $vnpayData,
            ]);
            $this->createRegistrationFromPayment($payment);
        }

        return response('')->setStatusCode(200)->send();
    }

    /**
     * Get payment status.
     *
     * GET /api/payments/{id}
     */
    public function getPaymentStatus(Request $request, $paymentId)
    {
        $user = $request->user();

        $payment = Payment::where('id', $paymentId)
            ->where('user_id', $user->id)
            ->first();

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        return response()->json([
            'id' => $payment->id,
            'status' => $payment->status,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'payment_method' => $payment->payment_method,
            'paid_at' => $payment->paid_at,
        ]);
    }

    /**
     * Check if user has completed payment for an event.
     *
     * GET /api/events/{eventId}/payment-status
     */
    public function checkPaymentStatus(Request $request, $eventId)
    {
        $user = $request->user();

        $payment = Payment::where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->where('status', Payment::STATUS_COMPLETED)
            ->first();

        if ($payment && $payment->registration) {
            return response()->json([
                'has_paid' => true,
                'registration_id' => $payment->registration->id,
                'status' => $payment->registration->status,
            ]);
        }

        return response()->json([
            'has_paid' => false,
            'pending_payment' => $payment ? [
                'id' => $payment->id,
                'status' => $payment->status,
            ] : null,
        ]);
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────────

    /**
     * Generate payment URL based on payment method.
     */
    private function generatePaymentUrl(Payment $payment, Event $event, int $quantity): ?string
    {
        switch ($payment->payment_method) {
            case Payment::METHOD_VNPAY:
                return $this->generateVnpayUrl($payment, $event, $quantity);

            case Payment::METHOD_PAYPAL:
                return $this->generatePaypalUrl($payment, $event);

            case Payment::METHOD_STRIPE:
            case Payment::METHOD_CREDIT_CARD:
                // Credit card payments are processed through Stripe
                return $this->generateStripeUrl($payment, $event);

            default:
                Log::error('Unknown payment method:', ['method' => $payment->payment_method]);
                return null;
        }
    }

    /**
     * Generate VNPay payment URL.
     */
    private function generateVnpayUrl(Payment $payment, Event $event, int $quantity): string
    {
        $config = config('vnpay');
        $urls = $config['urls'][$config['env']];

        $vnpayData = [
            'vnp_version' => $config['default_version'],
            'vnp_command' => 'pay',
            'vnp_terminal_id' => $config['terminal_id'],
            'vnp_amount' => (int)($payment->amount * 100), // VNPay uses cents
            'vnp_cust_info' => auth()->id(),
            'vnp_txn_ref' => $payment->id . '_' . time(), // Unique reference
            'vnp_order_info' => "Thanh toan su kien: {$event->name}",
            'vnp_order_type' => 'event',
            'vnp_locale' => $config['default_locale'],
            'vnp_create_date' => now()->format('YmdHis'),
            'vnp_expire_date' => now()->addMinutes(30)->format('YmdHis'),
            'vnp_return_url' => $config['return_url'],
            'vnp_ipn_url' => $config['ipn_url'],
            'vnp_merchant' => $config['merchant_id'],
        ];

        // Sort by key
        ksort($vnpayData);

        // Build URL with HMAC SHA256 signature
        $queryString = http_build_query($vnpayData);
        $signature = $this->generateVnpaySignature($vnpayData);

        // Update payment transaction_id
        $payment->update(['transaction_id' => $vnpayData['vnp_txn_ref']]);

        return $urls['payment'] . '?' . $queryString . '&vnp_secure_hash=' . $signature;
    }

    /**
     * Generate HMAC SHA256 signature for VNPay.
     */
    private function generateVnpaySignature(array $data): string
    {
        $config = config('vnpay');
        ksort($data);

        $signData = urldecode(http_build_query($data));

        return hash_hmac('sha256', $signData, $config['merchant_password']);
    }

    /**
     * Verify VNPay signature.
     */
    private function verifyVnpaySignature(array $data): bool
    {
        $config = config('vnpay');

        if (!isset($data['vnp_secure_hash'])) {
            return false;
        }

        $secureHash = $data['vnp_secure_hash'];
        unset($data['vnp_secure_hash']);

        ksort($data);
        $signData = urldecode(http_build_query($data));

        $calculatedHash = hash_hmac('sha256', $signData, $config['merchant_password']);

        return $secureHash === $calculatedHash;
    }

    /**
     * Generate PayPal URL (simplified).
     */
    private function generatePaypalUrl(Payment $payment, Event $event): string
    {
        // TODO: Implement PayPal OAuth flow
        // For now, placeholder - requires PayPal SDK setup
        return '#paypal-not-implemented';
    }

    /**
     * Generate Stripe URL (simplified).
     */
    private function generateStripeUrl(Payment $payment, Event $event): string
    {
        // TODO: Implement Stripe Checkout flow
        // For now, placeholder - requires Stripe SDK setup
        return '#stripe-not-implemented';
    }

    /**
     * Create registration after successful payment.
     */
    private function createRegistrationFromPayment(Payment $payment): void
    {
        // Check if registration already exists
        $existing = Registration::where('payment_id', $payment->id)->first();
        if ($existing) {
            return;
        }

        $event = $payment->event;
        $user = $payment->user;

        DB::transaction(function () use ($event, $user, $payment) {
            // Re-check capacity with lock
            $event = Event::lockForUpdate()->find($payment->event_id);

            if (!$event) {
                return;
            }

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
                    'payment_id' => $payment->id,
                ]);
            } else {
                // Event full, add to waitlist
                $nextPosition = Registration::where('event_id', $event->id)
                    ->whereNotNull('waitlist_position')
                    ->max('waitlist_position');
                $nextPosition = ($nextPosition ?? 0) + 1;

                $registration = Registration::create([
                    'event_id' => $event->id,
                    'attendee_id' => $user->id,
                    'status' => 'Waitlisted',
                    'waitlist_position' => $nextPosition,
                    'payment_id' => $payment->id,
                ]);
            }

            if (isset($registration)) {
                $payment->update(['registration_id' => $registration->id]);
            }
        });
    }
}
