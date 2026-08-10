<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('country', 100)->nullable()->after('email');
            $table->string('stay_reason', 50)->nullable()->after('country');
            $table->string('party_type', 20)->default('individual')->after('stay_reason');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['country', 'stay_reason', 'party_type']);
        });
    }
};
