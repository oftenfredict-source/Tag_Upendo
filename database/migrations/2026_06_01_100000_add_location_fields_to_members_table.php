<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('birth_mkoa')->nullable()->after('date_of_birth');
            $table->string('birth_wilaya')->nullable()->after('birth_mkoa');
            $table->string('residence_mkoa')->nullable()->after('birth_wilaya');
            $table->string('residence_wilaya')->nullable()->after('residence_mkoa');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'birth_mkoa',
                'birth_wilaya',
                'residence_mkoa',
                'residence_wilaya',
            ]);
        });
    }
};
