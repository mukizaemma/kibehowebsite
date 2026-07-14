<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'home_journey_title')) {
                $table->string('home_journey_title', 255)->nullable()->after('home_hero_lead');
            }
            if (! Schema::hasColumn('settings', 'home_journey_lead')) {
                $table->text('home_journey_lead')->nullable()->after('home_journey_title');
            }
            if (! Schema::hasColumn('settings', 'home_journey_image')) {
                $table->string('home_journey_image')->nullable()->after('home_journey_lead');
            }
        });

        Schema::create('home_journey_steps', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('icon', 100)->default('fa-solid fa-circle');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $defaults = [
            ['label' => 'Arrive in Kigali', 'icon' => 'fa-solid fa-plane-arrival', 'sort_order' => 1],
            ['label' => 'Travel to Kibeho', 'icon' => 'fa-solid fa-road', 'sort_order' => 2],
            ['label' => 'Visit the Shrine', 'icon' => 'fa-solid fa-church', 'sort_order' => 3],
            ['label' => 'Attend Mass', 'icon' => 'fa-solid fa-cross', 'sort_order' => 4],
            ['label' => 'Stay at Magnificat MV Hotel', 'icon' => 'fa-solid fa-bed', 'sort_order' => 5],
            ['label' => 'Explore southern Rwanda', 'icon' => 'fa-solid fa-mountain-sun', 'sort_order' => 6],
            ['label' => 'Return home refreshed', 'icon' => 'fa-solid fa-heart', 'sort_order' => 7],
        ];

        $now = now();
        foreach ($defaults as $step) {
            DB::table('home_journey_steps')->insert([
                'label' => $step['label'],
                'icon' => $step['icon'],
                'sort_order' => $step['sort_order'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('home_journey_steps');

        Schema::table('settings', function (Blueprint $table) {
            foreach (['home_journey_image', 'home_journey_lead', 'home_journey_title'] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
