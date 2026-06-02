<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('recurring_programs')) {
            return;
        }

        Schema::create('recurring_programs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedTinyInteger('weekday');
            $table->string('event_type')->default('service');
            $table->time('start_time')->default('18:00:00');
            $table->time('end_time')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('recurring_program_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_program_id')->constrained()->cascadeOnDelete();
            $table->date('service_date');
            $table->string('leader')->nullable();
            $table->timestamps();

            $table->unique(['recurring_program_id', 'service_date'], 'program_date_unique');
        });

        DB::table('recurring_programs')->insert([
            [
                'title' => 'Bible Study',
                'weekday' => 2,
                'event_type' => 'fellowship',
                'start_time' => '18:00:00',
                'end_time' => '19:30:00',
                'location' => null,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Prayer Meeting',
                'weekday' => 4,
                'event_type' => 'prayer',
                'start_time' => '18:00:00',
                'end_time' => '19:30:00',
                'location' => null,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_program_assignments');
        Schema::dropIfExists('recurring_programs');
    }
};
