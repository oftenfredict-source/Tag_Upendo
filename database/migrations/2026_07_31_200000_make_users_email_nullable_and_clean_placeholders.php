<?php

use App\Models\User;
use App\Services\MemberAccountService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        User::with('member')
            ->where('email', 'like', '%@members.tagupendo.local')
            ->each(function (User $user) {
                $email = $user->member
                    ? MemberAccountService::resolveUserEmail($user->member, $user)
                    : null;

                $user->update(['email' => $email]);
            });
    }

    public function down(): void
    {
        User::whereNull('email')->update([
            'email' => 'missing'.uniqid().'@members.tagupendo.local',
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};
