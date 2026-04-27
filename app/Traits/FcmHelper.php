<?php

namespace App\Traits;

use App\Models\Device;
use App\Models\NationalMatch;
use App\Models\NationalMatchEvent;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Str;

class FcmHelper
{
    protected static function messaging()
    {
        return (new Factory)
            ->withServiceAccount(storage_path(config('services.firebase.credentials')))
            ->createMessaging();
    }
    public static function sendToAllClients(string $title, string $body, array $data = []): void
    {
        $tokens = Device::whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->filter()
            ->values()
            ->toArray();

        if (empty($tokens)) {
            return;
        }

        // Optional: Chunk tokens if you have a lot
        $chunks = array_chunk($tokens, 500); // FCM max 500 per batch

        foreach ($chunks as $chunk) {
            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $report = self::messaging()->sendMulticast($message, $chunk);

            // Log failed tokens (optional)
            if ($report->hasFailures()) {
                foreach ($report->failures()->getItems() as $failure) {
                    \Log::warning("FCM Failure", [
                        'token' => $failure->target()->value(),
                        'error' => $failure->error()->getMessage(),
                    ]);
                }
            }
        }
    }


    /**
     * Send a notification to all tokens for a given user ID.
     */
    public static function sendToUser(int $userId, string $title, string $body, array $data = []): void
    {
        $tokens = Device::where('user_id', $userId)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->filter()
            ->values()
            ->toArray();

        if (empty($tokens)) {
            \Log::channel('payment')->info('FCM: No tokens for user', ['user_id' => $userId]);
            return;
        }

        $message = CloudMessage::new()
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        $report = self::messaging()->sendMulticast($message, $tokens);

        if ($report->hasFailures()) {
            foreach ($report->failures()->getItems() as $failure) {
                \Log::warning('FCM Failure (user)', [
                    'user_id' => $userId,
                    'token' => $failure->target()->value(),
                    'error' => $failure->error()->getMessage(),
                ]);
            }
        }
    }

    /* =====================
     | Payment Notifications
     |======================*/
    public static function sendPaymentSuccessToUser(int $userId, string $orderId, ?string $amount = null, ?string $currency = null): void
    {
        $data = [
            'type' => 'payment_success',
            'action' => 'navigate',
            'screen' => 'PaymentSuccess',
            'order_id' => $orderId,
        ];
        if ($amount !== null) {
            $data['amount'] = $amount;
        }
        if ($currency !== null) {
            $data['currency'] = $currency;
        }

        self::sendToUser($userId, 'Payment Successful', 'Your subscription is now active.', $data);
    }

    public static function sendPaymentFailedToUser(int $userId, string $orderId, ?string $status = null, ?string $message = null): void
    {
        $data = [
            'type' => 'payment_failed',
            'action' => 'navigate',
            'screen' => 'PaymentFailed',
            'order_id' => $orderId,
        ];
        if ($status !== null) {
            $data['status'] = $status;
        }
        if ($message !== null) {
            $data['message'] = $message;
        }

        self::sendToUser($userId, 'Payment Failed', $message ?? 'We could not complete your payment.', $data);
    }

    public static function sendPaymentCancelledToUser(int $userId, string $orderId): void
    {
        self::sendToUser($userId, 'Payment Cancelled', 'You cancelled the payment.', [
            'type' => 'payment_cancelled',
            'action' => 'navigate',
            'screen' => 'PaymentFailed',
            'order_id' => $orderId,
        ]);
    }
}
