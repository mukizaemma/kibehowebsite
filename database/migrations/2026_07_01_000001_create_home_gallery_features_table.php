<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_gallery_features', function (Blueprint $table) {
            $table->id();
            $table->string('image_key')->unique();
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->string('source_type', 32);
            $table->unsignedBigInteger('source_id');
            $table->unsignedTinyInteger('sort_order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_gallery_features');
    }
};
