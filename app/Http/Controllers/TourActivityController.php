<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TourActivity;
use App\Models\TourActivityImage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class TourActivityController extends Controller
{
    public function index()
    {
        $activities = TourActivity::with('images')->ordered()->get();

        return view('content-management.tour-activities.index', compact('activities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => admin_image_validation_rule(),
            'status' => 'required|in:Active,Inactive',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'images.*' => admin_image_validation_rule(),
        ]);

        $activity = new TourActivity();
        $activity->title = $request->title;
        $activity->slug = Str::slug($request->title);
        $activity->description = $request->description;
        $activity->status = $request->status;
        $activity->sort_order = (int) ($request->sort_order ?? 0);
        $activity->added_by = auth()->id();

        if ($request->hasFile('cover_image')) {
            $activity->cover_image = store_optimized_image($request->file('cover_image'), 'tour-activities');
        }

        $activity->save();

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                TourActivityImage::create([
                    'tour_activity_id' => $activity->id,
                    'image' => store_optimized_image($image, 'tour-activities/gallery'),
                    'order' => $index,
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Tour activity created successfully']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => admin_image_validation_rule(),
            'status' => 'required|in:Active,Inactive',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'images.*' => admin_image_validation_rule(),
        ]);

        $activity = TourActivity::findOrFail($id);
        $activity->title = $request->title;
        $activity->slug = Str::slug($request->title);
        $activity->description = $request->description;
        $activity->status = $request->status;
        $activity->sort_order = (int) ($request->sort_order ?? 0);

        if ($request->hasFile('cover_image')) {
            if ($activity->cover_image) {
                Storage::disk('public')->delete($activity->cover_image);
            }
            $activity->cover_image = store_optimized_image($request->file('cover_image'), 'tour-activities');
        }

        $activity->save();

        if ($request->hasFile('images')) {
            $baseOrder = (int) $activity->images()->max('order');
            foreach ($request->file('images') as $index => $image) {
                TourActivityImage::create([
                    'tour_activity_id' => $activity->id,
                    'image' => store_optimized_image($image, 'tour-activities/gallery'),
                    'order' => $baseOrder + $index + 1,
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Tour activity updated successfully']);
    }

    public function destroy($id)
    {
        $activity = TourActivity::findOrFail($id);
        
        // Delete cover image
        if ($activity->cover_image) {
            Storage::disk('public')->delete($activity->cover_image);
        }

        // Delete gallery images
        foreach ($activity->images as $image) {
            Storage::disk('public')->delete($image->image);
            $image->delete();
        }

        $activity->delete();

        return response()->json(['success' => true, 'message' => 'Tour activity deleted successfully']);
    }

    public function show($id)
    {
        $activity = TourActivity::with('images')->findOrFail($id);
        return response()->json($activity);
    }

    public function deleteImage($id)
    {
        $image = TourActivityImage::findOrFail($id);
        Storage::disk('public')->delete($image->image);
        $image->delete();

        return response()->json(['success' => true, 'message' => 'Image deleted successfully']);
    }
}
