<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nyaruguru_pages', function (Blueprint $table) {
            if (! Schema::hasColumn('nyaruguru_pages', 'home_title')) {
                $table->string('home_title', 255)->nullable()->after('description');
            }
            if (! Schema::hasColumn('nyaruguru_pages', 'home_lead')) {
                $table->text('home_lead')->nullable()->after('home_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('nyaruguru_pages', function (Blueprint $table) {
            if (Schema::hasColumn('nyaruguru_pages', 'home_lead')) {
                $table->dropColumn('home_lead');
            }
            if (Schema::hasColumn('nyaruguru_pages', 'home_title')) {
                $table->dropColumn('home_title');
            }
        });
    }
};
