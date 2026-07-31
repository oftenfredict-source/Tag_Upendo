<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tithes', function (Blueprint $table) {
            $table->string('member_name')->nullable()->after('member_id');
        });

        DB::table('tithes')
            ->whereNotNull('member_id')
            ->orderBy('id')
            ->each(function ($tithe) {
                $name = DB::table('members')->where('id', $tithe->member_id)->value('name');

                if ($name) {
                    DB::table('tithes')->where('id', $tithe->id)->update(['member_name' => $name]);
                }
            });

        Schema::table('tithes', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
        });

        DB::statement('ALTER TABLE tithes MODIFY member_id BIGINT UNSIGNED NULL');

        Schema::table('tithes', function (Blueprint $table) {
            $table->foreign('member_id')->references('id')->on('members')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tithes', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
        });

        DB::statement('ALTER TABLE tithes MODIFY member_id BIGINT UNSIGNED NOT NULL');

        Schema::table('tithes', function (Blueprint $table) {
            $table->foreign('member_id')->references('id')->on('members')->cascadeOnDelete();
            $table->dropColumn('member_name');
        });
    }
};
