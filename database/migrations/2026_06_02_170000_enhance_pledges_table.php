<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pledges', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->decimal('amount_paid', 15, 2)->default(0)->after('amount');
        });

        Schema::create('pledge_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pledge_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pledge_payments');

        Schema::table('pledges', function (Blueprint $table) {
            $table->dropConstrainedForeignId('member_id');
            $table->dropColumn('amount_paid');
        });
    }
};
