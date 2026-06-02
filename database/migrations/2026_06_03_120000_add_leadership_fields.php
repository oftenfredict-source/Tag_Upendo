<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'leader_member_id')) {
                $table->foreignId('leader_member_id')->nullable()->after('leader')->constrained('members')->nullOnDelete();
            }
        });

        Schema::table('church_services', function (Blueprint $table) {
            if (!Schema::hasColumn('church_services', 'leader')) {
                $table->string('leader')->nullable()->after('title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('leader_member_id');
        });

        Schema::table('church_services', function (Blueprint $table) {
            $table->dropColumn('leader');
        });
    }
};
