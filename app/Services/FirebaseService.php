<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Exception\MessagingException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $credentialsPath = config('firebase.credentials.file');
        
        if (!file_exists($credentialsPath)) {
            Log::error("Firebase credentials file not found: {$credentialsPath}");
            throw new \Exception("Firebase credentials file not found. Please download from Firebase Console.");
        }

        $factory = (new Factory)->withServiceAccount($credentialsPath);
        $this->messaging = $factory->createMessaging();
    }

    /**
     * Send notification to single device
     *
     * @param string $token FCM device token
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return bool Success status
     */
    public function sendToDevice(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $this->messaging->send($message);

            Log::info("FCM sent successfully", [
                'token' => substr($token, 0, 20) . '...',
                'title' => $title
            ]);

            return true;
        } catch (MessagingException $e) {
            Log::error("FCM Error: " . $e->getMessage(), [
                'token' => substr($token, 0, 20) . '...',
                'code' => $e->getCode()
            ]);

            // Handle invalid/expired tokens
            if ($this->isInvalidTokenError($e)) {
                $this->removeInvalidToken($token);
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Unexpected FCM Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to multiple devices
     *
     * @param array $tokens Array of FCM device tokens
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return array|bool Report or false on failure
     */
    public function sendToMultipleDevices(array $tokens, string $title, string $body, array $data = [])
    {
        if (empty($tokens)) {
            Log::warning("No tokens provided for multicast");
            return false;
        }

        try {
            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $report = $this->messaging->sendMulticast($message, $tokens);

            Log::info("FCM Multicast sent", [
                'success_count' => $report->successes()->count(),
                'failure_count' => $report->failures()->count(),
                'total_tokens' => count($tokens)
            ]);

            // Handle failed tokens
            foreach ($report->failures()->getItems() as $failure) {
                $failedToken = $failure->target()->value();
                Log::warning("Failed to send to token", [
                    'token' => substr($failedToken, 0, 20) . '...',
                    'error' => $failure->error()->getMessage()
                ]);
                $this->removeInvalidToken($failedToken);
            }

            return $report;
        } catch (MessagingException $e) {
            Log::error("FCM Multicast Error: " . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            Log::error("Unexpected Multicast Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to all admin users
     *
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @param int|null $exceptUserId Exclude specific user ID
     * @return array|bool Report or false on failure
     */
    public function sendToAllAdmins(string $title, string $body, array $data = [], ?int $exceptUserId = null)
    {
        $query = DB::table('fcm_tokens')
            ->join('users', 'fcm_tokens.user_id', '=', 'users.id')
            ->where('users.role', 'admin');

        if ($exceptUserId) {
            $query->where('users.id', '!=', $exceptUserId);
        }

        $tokens = $query->pluck('fcm_tokens.fcm_token')->toArray();

        if (empty($tokens)) {
            Log::warning("No admin tokens found");
            return false;
        }

        Log::info("Sending to admins", [
            'admin_count' => count($tokens),
            'except_user' => $exceptUserId
        ]);

        return $this->sendToMultipleDevices($tokens, $title, $body, $data);
    }

    /**
     * Send notification to specific user (all their devices)
     *
     * @param int $userId User ID
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return array|bool Report or false on failure
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = [])
    {
        $tokens = DB::table('fcm_tokens')
            ->where('user_id', $userId)
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            Log::warning("No tokens found for user", ['user_id' => $userId]);
            return false;
        }

        return $this->sendToMultipleDevices($tokens, $title, $body, $data);
    }

    /**
     * Check if error is related to invalid token
     *
     * @param MessagingException $e
     * @return bool
     */
    protected function isInvalidTokenError(MessagingException $e): bool
    {
        $errorMessage = strtolower($e->getMessage());
        
        return str_contains($errorMessage, 'not found') ||
               str_contains($errorMessage, 'invalid registration') ||
               str_contains($errorMessage, 'registration-token-not-registered') ||
               $e->getCode() === 404;
    }

    /**
     * Remove invalid token from database
     *
     * @param string $token
     * @return void
     */
    protected function removeInvalidToken(string $token): void
    {
        try {
            $deleted = DB::table('fcm_tokens')->where('fcm_token', $token)->delete();
            
            if ($deleted) {
                Log::info("Removed invalid FCM token", [
                    'token' => substr($token, 0, 20) . '...'
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to remove invalid token: " . $e->getMessage());
        }
    }

    /**
     * Get FCM token count for a user
     *
     * @param int $userId
     * @return int
     */
    public function getUserTokenCount(int $userId): int
    {
        return DB::table('fcm_tokens')
            ->where('user_id', $userId)
            ->count();
    }

    /**
     * Get all active admin tokens count
     *
     * @return int
     */
    public function getAdminTokenCount(): int
    {
        return DB::table('fcm_tokens')
            ->join('users', 'fcm_tokens.user_id', '=', 'users.id')
            ->where('users.role', 'admin')
            ->count();
    }
}
