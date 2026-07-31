<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $baseUrl;
    protected ?string $token;
    protected ?string $senderId;
    protected bool $enabled;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.sms.base_url', 'https://messaging-service.co.tz'), '/');
        $this->token = $this->resolveCredential('sms_token', 'services.sms.token', 'SMS_TOKEN');
        $this->senderId = $this->resolveCredential('sms_sender_id', 'services.sms.sender_id', 'SMS_SENDER_ID')
            ?: 'TAG UPENDO';
        $this->enabled = Setting::has('sms_enabled')
            ? Setting::bool('sms_enabled', true)
            : filter_var(config('services.sms.enabled', true), FILTER_VALIDATE_BOOL);
    }

    public function isEnabled(): bool
    {
        return $this->enabled && filled($this->token);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Backward compatibility wrapper for existing code:
     */
    public function sendSms(string $phoneNumber, string $message): bool
    {
        $result = $this->sendSingle($phoneNumber, $message);
        return $result['success'] ?? false;
    }

    /**
     * Send a single SMS to one recipient.
     *
     * @param  string  $to       Phone number starting with 255 (e.g. 255677155156)
     * @param  string  $message  Text message content
     * @return array{success: bool, message: string, data: mixed}
     */
    public function sendSingle(string $to, string $message): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'SMS imezimwa kwenye mipangilio.', 'data' => null];
        }

        $to = $this->formatPhone($to);

        if ($to === '') {
            return ['success' => false, 'message' => __('Invalid phone number. Use 07XXXXXXXX or 2557XXXXXXXX.'), 'data' => null];
        }

        try {
            $response = Http::withToken($this->token)
                ->acceptJson()
                ->asJson()
                ->post("{$this->baseUrl}/api/sms/v2/text/single", [
                    'from' => $this->senderId,
                    'to' => $to,
                    'text' => $message,
                ]);

            return $this->handleResponse($response, "to={$to}");

        } catch (\Throwable $e) {
            Log::error('[SmsService] HTTP error sending SMS', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => 'SMS request failed: ' . $e->getMessage(), 'data' => null];
        }
    }

    /**
     * Send SMS to multiple recipients.
     *
     * @param  array<string>  $recipients  Array of phone numbers starting with 255
     * @param  string         $message     Text message content
     * @return array
     */
    public function sendMultiple(array $recipients, string $message): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'SMS imezimwa kwenye mipangilio.', 'data' => null];
        }

        $recipients = array_values(array_filter(array_map(fn ($to) => $this->formatPhone($to), $recipients)));

        if ($recipients === []) {
            return ['success' => false, 'message' => __('No valid phone numbers found. Use 07XXXXXXXX or 2557XXXXXXXX.'), 'data' => null];
        }

        $messages = array_map(fn($to) => [
            'from' => $this->senderId,
            'to' => $this->formatPhone($to),
            'text' => $message,
        ], $recipients);

        try {
            $response = Http::withToken($this->token)
                ->acceptJson()
                ->asJson()
                ->post("{$this->baseUrl}/api/sms/v2/text/multi", [
                    'messages' => $messages,
                ]);

            return $this->handleResponse($response, 'bulk send (' . count($recipients) . ' recipients)');

        } catch (\Throwable $e) {
            Log::error('[SmsService] HTTP error sending bulk SMS', [
                'count' => count($recipients),
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => 'SMS request failed: ' . $e->getMessage(), 'data' => null];
        }
    }

    /**
     * Check remaining SMS balance.
     *
     * @return array{success: bool, balance: string|null, data: mixed}
     */
    public function getBalance(): array
    {
        try {
            $response = Http::withToken($this->token)
                ->acceptJson()
                ->get("{$this->baseUrl}/api/v2/balance");

            if ($response->successful()) {
                $data = $response->json();
                $balanceStr = $data['display'] ?? ($data['balance'] ?? '0');

                // Extract numeric value from "248.00 TSH"
                $numericBalance = (float) preg_replace('/[^0-9.]/', '', $balanceStr);
                $smsPrice = 16.0;
                $smsCount = floor($numericBalance / $smsPrice);

                return [
                    'success' => true,
                    'balance' => $balanceStr,
                    'sms_count' => $smsCount,
                    'data' => $data,
                ];
            }

            return ['success' => false, 'balance' => null, 'sms_count' => 0, 'data' => $response->json()];

        } catch (\Throwable $e) {
            Log::error('[SmsService] Failed to get SMS balance: ' . $e->getMessage());
            return ['success' => false, 'balance' => null, 'sms_count' => 0, 'data' => null];
        }
    }

    /**
     * Get sent SMS logs from the API.
     *
     * @param array $params Optional filters (from, to, sentSince, sentUntil, limit, offset)
     * @return array
     */
    public function getLogs(array $params = []): array
    {
        try {
            $response = Http::withToken($this->token)
                ->acceptJson()
                ->get("{$this->baseUrl}/api/sms/v1/logs", $params);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'logs' => $response->json()['results'] ?? [],
                    'data' => $response->json(),
                ];
            }

            return ['success' => false, 'logs' => [], 'data' => $response->json()];

        } catch (\Throwable $e) {
            Log::error('[SmsService] Failed to get SMS logs: ' . $e->getMessage());
            return ['success' => false, 'logs' => [], 'data' => null];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Normalize phone number to 255xxxxxxxxx format.
     */
    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (strlen($phone) === 10 && str_starts_with($phone, '0')) {
            $phone = '255' . substr($phone, 1);
        }

        if (strlen($phone) === 9) {
            $phone = '255' . $phone;
        }

        if (strlen($phone) !== 12 || ! str_starts_with($phone, '255')) {
            return '';
        }

        return $phone;
    }

    /**
     * Parse and log API response.
     */
    private function handleResponse($response, string $context): array
    {
        $status = $response->status();
        $data = $response->json();

        if ($response->successful()) {
            Log::info("[SmsService] SMS sent successfully ({$context})", ['status' => $status, 'response' => $data]);
            return ['success' => true, 'message' => 'SMS sent successfully.', 'data' => $data];
        }

        Log::warning("[SmsService] SMS failed ({$context})", ['status' => $status, 'response' => $data]);
        $errorMsg = $data['requestError']['serviceException']['text']
            ?? $data['message']
            ?? "API returned HTTP {$status}";

        return ['success' => false, 'message' => $errorMsg, 'data' => $data];
    }

    protected function resolveCredential(string $settingKey, string $configKey, string $envKey): ?string
    {
        $value = Setting::get($settingKey);

        if (filled($value)) {
            return trim((string) $value);
        }

        $value = config($configKey) ?: env($envKey);

        return filled($value) ? trim((string) $value) : null;
    }
}
