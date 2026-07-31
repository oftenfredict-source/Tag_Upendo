<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'theme')) {
                $table->string('theme')->nullable()->after('service_type');
            }
            if (! Schema::hasColumn('events', 'preacher_member_id')) {
                $table->foreignId('preacher_member_id')->nullable()->after('leader_member_id')->constrained('members')->nullOnDelete();
            }
            if (! Schema::hasColumn('events', 'coordinator_member_id')) {
                $table->foreignId('coordinator_member_id')->nullable()->after('preacher_member_id')->constrained('members')->nullOnDelete();
            }
            if (! Schema::hasColumn('events', 'elder_member_id')) {
                $table->foreignId('elder_member_id')->nullable()->after('coordinator_member_id')->constrained('members')->nullOnDelete();
            }
            if (! Schema::hasColumn('events', 'choir')) {
                $table->string('choir')->nullable()->after('location');
            }
            if (! Schema::hasColumn('events', 'registered_members_count')) {
                $table->unsignedInteger('registered_members_count')->nullable()->after('choir');
            }
            if (! Schema::hasColumn('events', 'guests_count')) {
                $table->unsignedInteger('guests_count')->nullable()->after('registered_members_count');
            }
            if (! Schema::hasColumn('events', 'scripture_readings')) {
                $table->text('scripture_readings')->nullable()->after('guests_count');
            }
            if (! Schema::hasColumn('events', 'announcements')) {
                $table->text('announcements')->nullable()->after('scripture_readings');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $columns = [
                'theme',
                'preacher_member_id',
                'coordinator_member_id',
                'elder_member_id',
                'choir',
                'registered_members_count',
                'guests_count',
                'scripture_readings',
                'announcements',
            ];

            foreach (['preacher_member_id', 'coordinator_member_id', 'elder_member_id'] as $fk) {
                if (Schema::hasColumn('events', $fk)) {
                    $table->dropConstrainedForeignId($fk);
                }
            }

            $drop = array_values(array_filter(
                ['theme', 'choir', 'registered_members_count', 'guests_count', 'scripture_readings', 'announcements'],
                fn ($col) => Schema::hasColumn('events', $col)
            ));

            if ($drop) {
                $table->dropColumn($drop);
            }
        });
    }
};
