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
                'title' => 'Prime Location',
                'description' => 'Located in Muhanga city, with easy access to healthcare facilities, business centers, schools, and tourist attractions.',
                'sort_order' => 1,
            ],
            [
                'title' => 'Faith-Based Environment',
                'description' => 'A peaceful and respectful atmosphere rooted in Catholic values, ideal for reflection, retreats, and calm stays.',
                'sort_order' => 2,
            ],
            [
                'title' => 'Comfortable & Affordable Rooms',
                'description' => 'Well-maintained rooms with modern amenities at competitive pricing for all types of travelers.',
                'sort_order' => 3,
            ],
            [
                'title' => 'Ideal for Conferences & Workshops',
                'description' => 'Fully equipped meeting spaces suitable for trainings, church gatherings, and corporate events.',
                'sort_order' => 4,
            ],
            [
                'title' => 'Professional & Caring Staff',
                'description' => 'A dedicated team committed to providing personalized service and exceeding guest expectations.',
                'sort_order' => 5,
            ],
            [
                'title' => 'Outside Catering Services',
                'description' => 'Reliable catering solutions available both within Muhanga and across Rwanda for events and group functions.',
                'sort_order' => 6,
            ],
            [
                'title' => 'Transport Assistance',
                'description' => 'Convenient transport arrangements for guests, including local and long-distance travel support.',
                'sort_order' => 7,
            ],
            [
                'title' => 'Safe & Serene Environment',
                'description' => 'A secure and quiet setting perfect for relaxation, focus, and spiritual retreats.',
                'sort_order' => 8,
            ],
        ];

        foreach ($items as $row) {
            WhyChooseUsItem::firstOrCreate(
                ['title' => $row['title']],
                ['description' => $row['description'], 'sort_order' => $row['sort_order']]
            );
        }
    }
}
