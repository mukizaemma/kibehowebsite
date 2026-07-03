<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('enquiry_type', 32)->default('general');
            $table->string('names');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->date('checkin_date')->nullable();
            $table->date('checkout_date')->nullable();
            $table->unsignedSmallInteger('adults')->nullable();
            $table->unsignedSmallInteger('children')->nullable();
            $table->text('admin_reply')->nullable();
            $table->string('reply_subject', 255)->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
