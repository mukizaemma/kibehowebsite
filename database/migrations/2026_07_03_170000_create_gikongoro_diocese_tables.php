<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gikongoro_diocese_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('header_image')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('stats_background_image')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('gikongoro_diocese_stats', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('value')->nullable();
            $table->string('icon', 100)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gikongoro_diocese_stats');
        Schema::dropIfExists('gikongoro_diocese_pages');
    }
};
