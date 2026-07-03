<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class HotelSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if settings table exists and is empty
        if (!Schema::hasTable('settings')) {
            return;
        }

        // Only seed if settings table is empty
        if (DB::table('settings')->count() > 0) {
            return;
        }

        $superAdmin = User::where('email', 'admin@lucernakabgayihotel.rw')->first();

        DB::table('settings')->insert([
            'title' => 'Kibeho Magnificat MV Hôtel',
            'company' => 'Kibeho Magnificat Hotel',
            'quote' => 'Faith • Hospitality • Peace',
            'address' => 'Nyaruguru, Kibeho, Rwanda',
            'phone' => '+250 788 000 000',
            'email' => 'info@kibeho-magnificat.rw',
            'facebook' => null,
            'instagram' => null,
            'twitter' => null,
            'youtube' => null,
            'linkedin' => null,
            'linktree' => null,
            'donate' => null,
            'logo' => null,
            'user_id' => $superAdmin ? $superAdmin->id : 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
