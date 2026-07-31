<?php

use App\Models\RegistrationLink;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_links', function (Blueprint $table) {
            $table->string('short_code', 12)->nullable()->unique()->after('token');
        });

        RegistrationLink::query()->whereNull('short_code')->each(function (RegistrationLink $link) {
            $link->update(['short_code' => RegistrationLink::generateUniqueShortCode()]);
        });
    }

    public function down(): void
    {
        Schema::table('registration_links', function (Blueprint $table) {
            $table->dropUnique(['short_code']);
            $table->dropColumn('short_code');
        });
    }
};
