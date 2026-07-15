<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sanctuary_events', function (Blueprint $table) {
            if (! Schema::hasColumn('sanctuary_events', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('title');
            }
        });

        Schema::table('nyaruguru_activities', function (Blueprint $table) {
            if (! Schema::hasColumn('nyaruguru_activities', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('title');
            }
        });

        Schema::create('sanctuary_event_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sanctuary_event_id')->constrained('sanctuary_events')->cascadeOnDelete();
            $table->string('image');
            $table->string('caption')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('nyaruguru_activity_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nyaruguru_activity_id')->constrained('nyaruguru_activities')->cascadeOnDelete();
            $table->string('image');
            $table->string('caption')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $this->backfillSlugs('sanctuary_events');
        $this->backfillSlugs('nyaruguru_activities');
    }

    public function down(): void
    {
        Schema::dropIfExists('nyaruguru_activity_images');
        Schema::dropIfExists('sanctuary_event_images');

        Schema::table('nyaruguru_activities', function (Blueprint $table) {
            if (Schema::hasColumn('nyaruguru_activities', 'slug')) {
                $table->dropColumn('slug');
            }
        });

        Schema::table('sanctuary_events', function (Blueprint $table) {
            if (Schema::hasColumn('sanctuary_events', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }

    private function backfillSlugs(string $table): void
    {
        $rows = DB::table($table)->select('id', 'title', 'slug')->get();
        $used = [];

        foreach ($rows as $row) {
            if (filled($row->slug)) {
                $used[$row->slug] = true;
                continue;
            }

            $base = Str::slug((string) $row->title) ?: 'activity-'.$row->id;
            $slug = $base;
            $i = 2;
            while (isset($used[$slug])) {
                $slug = $base.'-'.$i;
                $i++;
            }
            $used[$slug] = true;

            DB::table($table)->where('id', $row->id)->update(['slug' => $slug]);
        }
    }
};
