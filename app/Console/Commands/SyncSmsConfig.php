<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

class SyncSmsConfig extends Command
{
    protected $signature = 'sms:sync-config';

    protected $description = 'Sync SMS credentials from .env into system settings';

    public function handle(): int
    {
        $token = config('services.sms.token');
        $senderId = config('services.sms.sender_id');
        $enabled = filter_var(config('services.sms.enabled'), FILTER_VALIDATE_BOOL);

        if ($token) {
            Setting::set('sms_token', $token, 'sms');
            $this->info('SMS token synced.');
        } else {
            $this->warn('SMS_TOKEN is empty in .env — skipped token sync.');
        }

        if ($senderId) {
            Setting::set('sms_sender_id', $senderId, 'sms');
            $this->info('SMS sender ID synced: ' . $senderId);
        }

        Setting::set('sms_enabled', $enabled, 'sms');
        $this->info('SMS enabled: ' . ($enabled ? 'yes' : 'no'));

        return self::SUCCESS;
    }
}
