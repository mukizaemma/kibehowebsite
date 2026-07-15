<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'home_hero_text_mode')) {
                $table->string('home_hero_text_mode', 20)
                    ->default('global')
                    ->after('home_hero_lead');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'home_hero_text_mode')) {
                $table->dropColumn('home_hero_text_mode');
            }
        });
    }
};
