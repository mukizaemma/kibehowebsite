<?php

use App\Models\Facility;
use App\Models\Facilityimage;
use App\Models\KibehoPage;
use App\Models\KibehoPageImage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'title' => 'Explore Kibeho Sanctuary',
            'description' => '<p>Welcome to Kibeho, a place of pilgrimage and peace in the hills of southern Rwanda. Our Lady of Kibeho is known worldwide as a sanctuary of prayer, reconciliation, and hope.</p><p>Stay with us at Kibeho Magnificat MV Hôtel and walk in the footsteps of faith — from the Apparition Hill to the Shrine of Our Lady of Sorrows.</p>',
            'official_website_url' => 'https://www.kibeho.org/',
            'status' => 'Active',
        ];

        $facility = Facility::with('images')->where('slug', 'explore-kibeho')->first();

        if ($facility) {
            $defaults['title'] = $facility->title;
            $defaults['description'] = $facility->description;
            $defaults['cover_image'] = $facility->cover_image;
            $defaults['official_website_url'] = $facility->official_website_url ?: $defaults['official_website_url'];
            $defaults['status'] = $facility->status;
        }

        $page = KibehoPage::query()->create($defaults);

        if ($facility) {
            foreach ($facility->images as $index => $image) {
                KibehoPageImage::create([
                    'kibeho_page_id' => $page->id,
                    'image' => $image->image,
                    'caption' => $image->caption,
                    'sort_order' => $index,
                ]);
            }

            $facility->images()->delete();
            $facility->delete();
        }

        if (Schema::hasColumn('facilities', 'official_website_url')) {
            Schema::table('facilities', function (Blueprint $table) {
                $table->dropColumn('official_website_url');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('facilities', 'official_website_url')) {
            Schema::table('facilities', function (Blueprint $table) {
                $table->string('official_website_url', 500)->nullable()->after('description');
            });
        }

        $page = KibehoPage::with('images')->first();
        if ($page) {
            Facility::create([
                'title' => $page->title,
                'slug' => 'explore-kibeho',
                'description' => $page->description,
                'cover_image' => $page->cover_image,
                'official_website_url' => $page->official_website_url,
                'status' => $page->status,
            ]);
            $page->images()->delete();
            $page->delete();
        }
    }
};
