<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('why_choose_us_items', function (Blueprint $table) {
            if (! Schema::hasColumn('why_choose_us_items', 'icon')) {
                $table->string('icon', 100)->nullable()->after('description');
            }
        });

        // Remove transport — hotel does not offer this service
        DB::table('why_choose_us_items')
            ->where('title', 'like', '%Transport%')
            ->delete();

        $pilgrimageItems = [
            [
                'title' => 'Near the Sanctuary',
                'description' => 'Stay in Kibeho close to the holy shrine — a peaceful base for pilgrimage, prayer and visits to the Marian sites.',
                'icon' => 'fa-solid fa-place-of-worship',
                'sort_order' => 1,
            ],
            [
                'title' => 'Faith-centred hospitality',
                'description' => 'A respectful Catholic atmosphere suited to pilgrims, retreatants and guests seeking quiet reflection.',
                'icon' => 'fa-solid fa-hands-praying',
                'sort_order' => 2,
            ],
            [
                'title' => 'Rest after pilgrimage',
                'description' => 'Comfortable, affordable rooms prepared for rest after a day of prayer, Mass and walking the sacred grounds.',
                'icon' => 'fa-solid fa-bed',
                'sort_order' => 3,
            ],
            [
                'title' => 'Retreats & gatherings',
                'description' => 'Meeting spaces for church retreats, choir camps, youth gatherings and prayerful workshops near the Sanctuary.',
                'icon' => 'fa-solid fa-people-group',
                'sort_order' => 4,
            ],
            [
                'title' => 'Warm, caring staff',
                'description' => 'A dedicated team that welcomes pilgrims and groups with kindness, guidance and attentive service.',
                'icon' => 'fa-solid fa-heart',
                'sort_order' => 5,
            ],
            [
                'title' => 'Meals for guests & groups',
                'description' => 'Simple, welcoming dining for individual guests and groups during pilgrimages, retreats and celebrations.',
                'icon' => 'fa-solid fa-utensils',
                'sort_order' => 6,
            ],
            [
                'title' => 'Safe & serene setting',
                'description' => 'A calm, secure place in southern Rwanda where faith, rest and community can unfold without distraction.',
                'icon' => 'fa-solid fa-shield-heart',
                'sort_order' => 7,
            ],
        ];

        $existing = DB::table('why_choose_us_items')->orderBy('sort_order')->orderBy('id')->get();

        if ($existing->isEmpty()) {
            $now = now();
            foreach ($pilgrimageItems as $row) {
                DB::table('why_choose_us_items')->insert($row + [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            return;
        }

        // Refresh legacy conference/transport-era copy to pilgrimage focus (match by old titles or order)
        $titleMap = [
            'Prime Location' => $pilgrimageItems[0],
            'Faith-Based Environment' => $pilgrimageItems[1],
            'Comfortable & Affordable Rooms' => $pilgrimageItems[2],
            'Ideal for Conferences & Workshops' => $pilgrimageItems[3],
            'Professional & Caring Staff' => $pilgrimageItems[4],
            'Outside Catering Services' => $pilgrimageItems[5],
            'Safe & Serene Environment' => $pilgrimageItems[6],
        ];

        foreach ($existing as $item) {
            if (isset($titleMap[$item->title])) {
                $fresh = $titleMap[$item->title];
                DB::table('why_choose_us_items')->where('id', $item->id)->update([
                    'title' => $fresh['title'],
                    'description' => $fresh['description'],
                    'icon' => $fresh['icon'],
                    'sort_order' => $fresh['sort_order'],
                    'updated_at' => now(),
                ]);
            } elseif (empty($item->icon)) {
                DB::table('why_choose_us_items')->where('id', $item->id)->update([
                    'icon' => 'fa-solid fa-circle-dot',
                    'updated_at' => now(),
                ]);
            }
        }

        // Ensure all pilgrimage defaults exist (in case some old titles were already edited)
        foreach ($pilgrimageItems as $row) {
            $found = DB::table('why_choose_us_items')->where('title', $row['title'])->exists();
            if (! $found) {
                // Only insert missing ones if we still have few items after transport delete
                $count = DB::table('why_choose_us_items')->count();
                if ($count < 7) {
                    DB::table('why_choose_us_items')->insert($row + [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('why_choose_us_items', function (Blueprint $table) {
            if (Schema::hasColumn('why_choose_us_items', 'icon')) {
                $table->dropColumn('icon');
            }
        });
    }
};
