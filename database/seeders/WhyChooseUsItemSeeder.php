<?php

namespace Database\Seeders;

use App\Models\WhyChooseUsItem;
use Illuminate\Database\Seeder;

class WhyChooseUsItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
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

        foreach ($items as $row) {
            WhyChooseUsItem::updateOrCreate(
                ['title' => $row['title']],
                [
                    'description' => $row['description'],
                    'icon' => $row['icon'],
                    'sort_order' => $row['sort_order'],
                ]
            );
        }

        WhyChooseUsItem::query()
            ->where('title', 'like', '%Transport%')
            ->delete();
    }
}
