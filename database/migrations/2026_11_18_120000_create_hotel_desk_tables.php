<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN status VARCHAR(32) NOT NULL DEFAULT 'pending'");
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            $row = DB::selectOne("select sql as sql from sqlite_master where type = 'table' and name = 'bookings'");
            $sql = preg_replace('/check\s*\(\s*"status"\s+in\s*\([^)]*\)\s*\)/i', '', (string) ($row->sql ?? ''));
            if ($sql !== '' && $sql !== (string) ($row->sql ?? '')) {
                DB::statement('PRAGMA foreign_keys = OFF');
                DB::statement('ALTER TABLE bookings RENAME TO bookings_status_old');
                DB::statement($sql);
                DB::statement('INSERT INTO bookings SELECT * FROM bookings_status_old');
                DB::statement('DROP TABLE bookings_status_old');
                DB::statement('PRAGMA foreign_keys = ON');
            }
        }

        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'currency')) {
                $table->string('currency', 8)->default('usd');
            }
            if (! Schema::hasColumn('bookings', 'reference')) {
                $table->string('reference', 32)->nullable()->unique();
            }
            if (! Schema::hasColumn('bookings', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
            }
        });

        Schema::table('rooms', function (Blueprint $table) {
            if (! Schema::hasColumn('rooms', 'overbook')) {
                $table->unsignedInteger('overbook')->default(0);
            }
        });

        Schema::table('restaurants', function (Blueprint $table) {
            if (! Schema::hasColumn('restaurants', 'requires_reservation')) {
                $table->boolean('requires_reservation')->default(false);
            }
            if (! Schema::hasColumn('restaurants', 'minimum_amount')) {
                $table->decimal('minimum_amount', 12, 2)->default(0);
            }
            if (! Schema::hasColumn('restaurants', 'minimum_amount_local')) {
                $table->decimal('minimum_amount_local', 12, 2)->default(0);
            }
            if (! Schema::hasColumn('restaurants', 'momo_code')) {
                $table->string('momo_code')->nullable();
            }
            if (! Schema::hasColumn('restaurants', 'momo_account_name')) {
                $table->string('momo_account_name')->nullable();
            }
        });

        Schema::create('user_module_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('module', 32);
            $table->timestamps();
            $table->unique(['user_id', 'module']);
        });

        Schema::create('user_operation_denials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('operation', 64);
            $table->timestamps();
            $table->unique(['user_id', 'operation']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 64);
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('summary');
            $table->json('payload')->nullable();
            $table->string('ip', 64)->nullable();
            $table->timestamps();
        });

        Schema::create('room_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->date('night');
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['room_id', 'night']);
        });

        Schema::create('booking_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 8);
            $table->string('method', 32)->default('cash');
            $table->string('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('booking_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 8);
            $table->string('source', 32)->default('extra');
            $table->unsignedBigInteger('restaurant_bill_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('removed_at')->nullable();
            $table->unsignedBigInteger('removed_by')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_settings', function (Blueprint $table) {
            $table->id();
            $table->string('heading')->nullable();
            $table->text('footer')->nullable();
            $table->timestamps();
        });

        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_category_id')->nullable()->constrained('menu_categories')->nullOnDelete();
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 8)->default('usd');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('restaurant_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('guest_name');
            $table->string('phone')->nullable();
            $table->unsignedInteger('party_size')->default(1);
            $table->dateTime('reserved_for');
            $table->string('status', 32)->default('booked');
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('restaurant_bills', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->string('status', 32)->default('open');
            $table->string('guest_name');
            $table->string('currency', 8);
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('restaurant_reservation_id')->nullable()->constrained('restaurant_reservations')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('restaurant_bill_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_bill_id')->constrained('restaurant_bills')->cascadeOnDelete();
            $table->foreignId('menu_item_id')->nullable()->constrained('menu_items')->nullOnDelete();
            $table->string('item_name');
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('currency', 8);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('restaurant_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_bill_id')->constrained('restaurant_bills')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 8);
            $table->string('method', 32)->default('cash');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('booking_charges', function (Blueprint $table) {
            $table->foreign('restaurant_bill_id')->references('id')->on('restaurant_bills')->nullOnDelete();
            $table->foreign('removed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('booking_charges', function (Blueprint $table) {
            $table->dropForeign(['restaurant_bill_id']);
            $table->dropForeign(['removed_by']);
        });

        Schema::dropIfExists('restaurant_payments');
        Schema::dropIfExists('restaurant_bill_lines');
        Schema::dropIfExists('restaurant_bills');
        Schema::dropIfExists('restaurant_reservations');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menu_categories');
        Schema::dropIfExists('invoice_settings');
        Schema::dropIfExists('booking_charges');
        Schema::dropIfExists('booking_payments');
        Schema::dropIfExists('room_closures');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('user_operation_denials');
        Schema::dropIfExists('user_module_grants');

        Schema::table('restaurants', function (Blueprint $table) {
            foreach (['requires_reservation', 'minimum_amount', 'minimum_amount_local', 'momo_code', 'momo_account_name'] as $column) {
                if (Schema::hasColumn('restaurants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'overbook')) {
                $table->dropColumn('overbook');
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            foreach (['currency', 'reference', 'created_by'] as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
