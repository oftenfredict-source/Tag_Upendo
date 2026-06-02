<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('gender', 20)->nullable()->after('email');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->string('address')->nullable()->after('date_of_birth');
            $table->string('marital_status', 20)->nullable()->after('address');
            $table->date('date_joined')->nullable()->after('marital_status');
            $table->date('baptism_date')->nullable()->after('date_joined');
            $table->string('occupation')->nullable()->after('baptism_date');
            $table->string('member_type', 30)->default('member')->after('occupation');
            $table->string('emergency_contact_name')->nullable()->after('member_type');
            $table->string('emergency_contact_phone', 20)->nullable()->after('emergency_contact_name');
            $table->text('notes')->nullable()->after('emergency_contact_phone');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'gender',
                'date_of_birth',
                'address',
                'marital_status',
                'date_joined',
                'baptism_date',
                'occupation',
                'member_type',
                'emergency_contact_name',
                'emergency_contact_phone',
                'notes',
            ]);
        });
    }
};
