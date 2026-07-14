<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'home_hero_headline')) {
                $table->string('home_hero_headline', 255)->nullable()->after('slideshow_cta_label');
            }
            if (! Schema::hasColumn('settings', 'home_hero_lead')) {
                $table->text('home_hero_lead')->nullable()->after('home_hero_headline');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'home_hero_lead')) {
                $table->dropColumn('home_hero_lead');
            }
            if (Schema::hasColumn('settings', 'home_hero_headline')) {
                $table->dropColumn('home_hero_headline');
            }
        });
    }
};
