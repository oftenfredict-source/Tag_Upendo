<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leadership_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_sw')->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('member_leadership_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leadership_role_id')->constrained()->cascadeOnDelete();
            $table->date('assigned_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['member_id', 'leadership_role_id'], 'member_role_unique');
        });

        $now = now();
        $roles = [
            ['name' => 'Senior Pastor', 'name_sw' => 'Mchungaji Mkuu', 'sort_order' => 1],
            ['name' => 'Assistant Pastor', 'name_sw' => 'Msaidizi wa Mchungaji', 'sort_order' => 2],
            ['name' => 'Elder', 'name_sw' => 'Mzee wa Kanisa', 'sort_order' => 3],
            ['name' => 'Deacon', 'name_sw' => 'Shemasi', 'sort_order' => 4],
            ['name' => 'Church Secretary', 'name_sw' => 'Katibu wa Kanisa', 'sort_order' => 5],
            ['name' => 'Treasurer', 'name_sw' => 'Mhazini', 'sort_order' => 6],
            ['name' => 'Worship Leader', 'name_sw' => 'Kiongozi wa Ibada', 'sort_order' => 7],
            ['name' => 'Youth Leader', 'name_sw' => 'Kiongozi wa Vijana', 'sort_order' => 8],
            ['name' => 'Women Leader', 'name_sw' => 'Kiongozi wa Wanawake', 'sort_order' => 9],
            ['name' => 'Men Leader', 'name_sw' => 'Kiongozi wa Wanaume', 'sort_order' => 10],
            ['name' => 'Bible Study Leader', 'name_sw' => 'Kiongozi wa Somo la Biblia', 'sort_order' => 11],
            ['name' => 'Prayer Leader', 'name_sw' => 'Kiongozi wa Maombi', 'sort_order' => 12],
            ['name' => 'Usher', 'name_sw' => 'Karani / Mpokeaji', 'sort_order' => 13],
        ];

        foreach ($roles as $role) {
            DB::table('leadership_roles')->insert(array_merge($role, [
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('member_leadership_role');
        Schema::dropIfExists('leadership_roles');
    }
};
