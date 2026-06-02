<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->boolean('is_baptized')->default(false)->after('date_joined');
        });

        \Illuminate\Support\Facades\DB::table('members')
            ->whereNotNull('baptism_date')
            ->update(['is_baptized' => true]);
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('is_baptized');
        });
    }
};
