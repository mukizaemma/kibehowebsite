<?php

namespace App\Http\Controllers;

use App\Models\HomeJourneyStep;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeJourneyController extends Controller
{
    public function index()
    {
        $setting = Setting::first();
        $steps = HomeJourneyStep::query()->ordered()->get();

        return view('content-management.home-journey.index', compact('setting', 'steps'));
    }

    public function updateIntro(Request $request)
    {
        $data = $request->validate([
            'home_journey_title' => 'nullable|string|max:255',
            'home_journey_lead' => 'nullable|string|max:2000',
            'home_journey_image' => admin_image_validation_rule(),
            'remove_home_journey_image' => 'nullable|boolean',
        ]);

        $setting = Setting::firstOrFail();
        $setting->home_journey_title = $data['home_journey_title'] ?? null;
        $setting->home_journey_lead = $data['home_journey_lead'] ?? null;

        if ($request->boolean('remove_home_journey_image') && $setting->home_journey_image) {
            Storage::disk('public')->delete($setting->home_journey_image);
            $setting->home_journey_image = null;
        }

        if ($request->hasFile('home_journey_image')) {
            if ($setting->home_journey_image) {
                Storage::disk('public')->delete($setting->home_journey_image);
            }
            $setting->home_journey_image = store_optimized_image($request->file('home_journey_image'), 'home-journey');
        }

        $setting->save();

        return redirect()
            ->route('content-management.home-journey.index')
            ->with('success', 'Journey section updated successfully.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string|max:255',
            'icon' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
        ]);

        $data['sort_order'] = $data['sort_order'] ?? ((int) HomeJourneyStep::max('sort_order') + 1);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['icon'] = $this->normalizeIcon($data['icon']);

        HomeJourneyStep::create($data);

        return response()->json(['success' => true, 'message' => 'Step created successfully']);
    }

    public function show($id)
    {
        return response()->json(HomeJourneyStep::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'label' => 'required|string|max:255',
            'icon' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
        ]);

        $step = HomeJourneyStep::findOrFail($id);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);
        $data['icon'] = $this->normalizeIcon($data['icon']);
        $step->update($data);

        return response()->json(['success' => true, 'message' => 'Step updated successfully']);
    }

    public function destroy($id)
    {
        HomeJourneyStep::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Step deleted successfully']);
    }

    private function normalizeIcon(string $icon): string
    {
        $icon = trim($icon);
        if ($icon === '') {
            return 'fa-solid fa-circle';
        }
        if (! str_contains($icon, ' ')) {
            return 'fa-solid '.$icon;
        }

        return $icon;
    }
}
