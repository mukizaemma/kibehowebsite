<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gikongoro_diocese_pages', function (Blueprint $table) {
            if (! Schema::hasColumn('gikongoro_diocese_pages', 'official_website_url')) {
                $table->string('official_website_url', 500)->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('gikongoro_diocese_pages', function (Blueprint $table) {
            if (Schema::hasColumn('gikongoro_diocese_pages', 'official_website_url')) {
                $table->dropColumn('official_website_url');
            }
        });
    }
};
