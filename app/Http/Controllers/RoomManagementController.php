<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Amenity;
use App\Models\Room;
use App\Models\Roomimage;
use App\Models\Setting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class RoomManagementController extends Controller
{
    public function index()
    {
        $rooms = Room::with(['amenities', 'images'])->latest()->get();
        $amenities = Amenity::all();
        $setting = Setting::first();

        return view('content-management.rooms.index', compact('rooms', 'amenities', 'setting'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cover_image' => admin_image_validation_rule(),
            'status' => 'required|in:Active,Inactive',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
            'images.*' => admin_image_validation_rule(),
        ]);

        $room = new Room();
        $room->title = $request->title;
        $room->slug = Str::slug($request->title);
        $room->description = $request->description;
        $room->room_type = 'room';
        $room->price = $request->price;
        $room->couplePrice = null;
        $room->number_of_rooms = 1;
        $room->guests_included_in_price = 2;
        $room->extra_adult_price = null;
        $room->extra_child_price = null;
        $room->extra_bed_price = null;
        $room->max_occupancy = 2;
        $room->bed_count = 1;
        $room->bed_type = null;
        $room->status = $request->status;
        $room->room_status = 'available';
        $room->user_id = auth()->id();

        if ($request->hasFile('cover_image')) {
            $room->cover_image = store_optimized_image($request->file('cover_image'), 'rooms');
        }

        $room->save();

        // Attach amenities
        if ($request->has('amenities')) {
            $room->amenities()->sync($request->amenities);
        }

        // Handle gallery images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                Roomimage::create([
                    'room_id' => $room->id,
                    'image' => store_optimized_image($image, 'rooms/gallery'),
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Room created successfully']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cover_image' => admin_image_validation_rule(),
            'status' => 'required|in:Active,Inactive',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
            'images.*' => admin_image_validation_rule(),
        ]);

        $room = Room::findOrFail($id);

        $room->title = $request->title;
        $room->slug = Str::slug($request->title);
        $room->description = $request->description;
        $room->room_type = 'room';
        $room->price = $request->price;
        $room->status = $request->status;

        if ($request->hasFile('cover_image')) {
            if ($room->cover_image) {
                Storage::disk('public')->delete($room->cover_image);
            }
            $room->cover_image = store_optimized_image($request->file('cover_image'), 'rooms');
        }

        $room->save();

        // Sync amenities
        if ($request->has('amenities')) {
            $room->amenities()->sync($request->amenities);
        } else {
            $room->amenities()->detach();
        }

        // Handle new gallery images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                Roomimage::create([
                    'room_id' => $room->id,
                    'image' => store_optimized_image($image, 'rooms/gallery'),
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Room updated successfully']);
    }

    public function destroy($id)
    {
        $room = Room::findOrFail($id);
        
        // Delete cover image
        if ($room->cover_image) {
            Storage::disk('public')->delete($room->cover_image);
        }

        // Delete gallery images
        foreach ($room->images as $image) {
            Storage::disk('public')->delete($image->image);
            $image->delete();
        }

        $room->delete();

        return response()->json(['success' => true, 'message' => 'Room deleted successfully']);
    }

    public function show($id)
    {
        $room = Room::with(['amenities', 'images'])->findOrFail($id);
        return response()->json($room);
    }

    public function deleteImage($id)
    {
        $image = Roomimage::findOrFail($id);
        Storage::disk('public')->delete($image->image);
        $image->delete();

        return response()->json(['success' => true, 'message' => 'Image deleted successfully']);
    }

    public function addImages(Request $request, $id)
    {
        $request->validate([
            'images.*' => 'required|image|max:2048',
        ]);

        $room = Room::findOrFail($id);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                Roomimage::create([
                    'room_id' => $room->id,
                    'image' => store_optimized_image($image, 'rooms/gallery'),
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Images added successfully']);
    }
}
