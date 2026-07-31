<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'service_group_id')) {
                $table->string('service_group_id', 36)->nullable()->after('id')->index();
            }
        });

        // Backfill existing services so each becomes its own group
        $services = DB::table('events')->where('event_type', 'service')->whereNull('service_group_id')->get(['id']);
        foreach ($services as $service) {
            DB::table('events')->where('id', $service->id)->update([
                'service_group_id' => (string) Str::uuid(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'service_group_id')) {
                $table->dropColumn('service_group_id');
            }
        });
    }
};
