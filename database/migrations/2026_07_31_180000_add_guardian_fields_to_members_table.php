<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('guardian_name')->nullable()->after('parent_id');
            $table->string('guardian_phone', 20)->nullable()->after('guardian_name');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['guardian_name', 'guardian_phone']);
        });
    }
};
