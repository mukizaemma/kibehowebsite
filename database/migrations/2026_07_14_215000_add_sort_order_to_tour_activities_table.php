<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_activities', function (Blueprint $table) {
            if (! Schema::hasColumn('tour_activities', 'sort_order')) {
                $table->unsignedSmallInteger('sort_order')->default(0)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tour_activities', function (Blueprint $table) {
            if (Schema::hasColumn('tour_activities', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
};
