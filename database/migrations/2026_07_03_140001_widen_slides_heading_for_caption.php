<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->string('heading', 500)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->string('heading', 255)->nullable()->change();
        });
    }
};
