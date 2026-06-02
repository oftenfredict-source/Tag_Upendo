<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->string('label')->nullable();
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            ['key' => 'church_name', 'value' => 'TAG Upendo', 'group' => 'church', 'label' => 'Church Name'],
            ['key' => 'church_tagline', 'value' => 'Follow Up System', 'group' => 'church', 'label' => 'Tagline'],
            ['key' => 'church_phone', 'value' => '', 'group' => 'church', 'label' => 'Phone'],
            ['key' => 'church_email', 'value' => '', 'group' => 'church', 'label' => 'Email'],
            ['key' => 'church_address', 'value' => '', 'group' => 'church', 'label' => 'Address'],
            ['key' => 'church_pastor', 'value' => '', 'group' => 'church', 'label' => 'Pastor Name'],
            ['key' => 'sms_token', 'value' => '', 'group' => 'sms', 'label' => 'SMS API Token'],
            ['key' => 'sms_sender_id', 'value' => 'TAG UPENDO', 'group' => 'sms', 'label' => 'SMS Sender ID'],
            ['key' => 'sms_enabled', 'value' => '1', 'group' => 'sms', 'label' => 'SMS Enabled'],
            ['key' => 'currency', 'value' => 'TSH', 'group' => 'general', 'label' => 'Currency Code'],
            ['key' => 'timezone', 'value' => 'Africa/Dar_es_Salaam', 'group' => 'general', 'label' => 'Timezone'],
            ['key' => 'date_format', 'value' => 'd/m/Y', 'group' => 'general', 'label' => 'Date Format'],
        ];

        foreach ($defaults as $row) {
            DB::table('settings')->insert(array_merge($row, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
