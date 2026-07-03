<?php

use App\Models\Facility;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Facility::where('slug', 'explore-kibeho')->exists()) {
            return;
        }

        Facility::create([
            'title' => 'Explore Kibeho Sanctuary',
            'slug' => 'explore-kibeho',
            'description' => '<p>Welcome to Kibeho, a place of pilgrimage and peace in the hills of southern Rwanda. Our Lady of Kibeho is known worldwide as a sanctuary of prayer, reconciliation, and hope.</p><p>Stay with us at Kibeho Magnificat MV Hôtel and walk in the footsteps of faith — from the Apparition Hill to the Shrine of Our Lady of Sorrows.</p>',
            'official_website_url' => 'https://www.kibeho.org/',
            'status' => 'Active',
            'added_by' => null,
        ]);
    }

    public function down(): void
    {
        Facility::where('slug', 'explore-kibeho')->delete();
    }
};
