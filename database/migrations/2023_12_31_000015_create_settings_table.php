<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('company')->nullable();
            $table->text('address')->nullable();
            $table->text('google_map_embed')->nullable();
            $table->string('phone')->nullable();
            $table->string('reception_phone')->nullable();
            $table->string('manager_phone')->nullable();
            $table->string('restaurant_phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->string('donate')->nullable();
            $table->text('deliveryInfo')->nullable();
            $table->boolean('footer_delivered_by_enabled')->default(true);
            $table->string('footer_delivered_by_company', 255)->nullable();
            $table->string('footer_delivered_by_url', 500)->nullable();
            $table->text('quote')->nullable();
            $table->unsignedTinyInteger('star_rating')->nullable();
            $table->string('keywords')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            $table->string('youtube')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('linktree')->nullable();
            $table->text('booking_com_url')->nullable();
            $table->decimal('booking_com_review_score', 3, 1)->nullable();
            $table->unsignedInteger('booking_com_review_count')->nullable();
            $table->text('booking_com_review_summary')->nullable();
            $table->text('booking_com_write_review_url')->nullable();
            $table->string('tripadvisor_location_id', 32)->nullable();
            $table->text('tripadvisor_hotel_url')->nullable();
            $table->text('tripadvisor_write_review_url')->nullable();
            $table->decimal('tripadvisor_review_score', 2, 1)->nullable();
            $table->unsignedInteger('tripadvisor_review_count')->nullable();
            $table->text('tripadvisor_review_summary')->nullable();
            $table->text('google_place_url')->nullable();
            $table->text('google_maps_embed_url')->nullable();
            $table->decimal('google_review_score', 2, 1)->nullable();
            $table->unsignedInteger('google_review_count')->nullable();
            $table->text('google_review_summary')->nullable();
            $table->text('google_write_review_url')->nullable();
            $table->string('whatsapp_e164', 32)->nullable();
            $table->text('whatsapp_default_message')->nullable();
            $table->string('channel_contact_email')->nullable();
            $table->string('ga4_measurement_id', 32)->nullable();
            $table->text('ga4_reports_url')->nullable();
            $table->string('price_currency', 8)->default('usd');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
