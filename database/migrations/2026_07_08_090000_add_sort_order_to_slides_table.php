<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('slides', 'sort_order')) {
            Schema::table('slides', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->default(0)->after('image');
            });
        }

        // Backfill existing slides so the manual order starts oldest-first.
        $position = 1;
        DB::table('slides')->orderBy('created_at')->orderBy('id')->get(['id'])->each(function ($slide) use (&$position) {
            DB::table('slides')->where('id', $slide->id)->update(['sort_order' => $position]);
            $position++;
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('slides', 'sort_order')) {
            Schema::table('slides', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
