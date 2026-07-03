<?php

namespace App\Http\Controllers;

use App\Models\SanctuaryEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SanctuaryEventController extends Controller
{
    public function index()
    {
        $events = SanctuaryEvent::query()->ordered()->get();

        return view('content-management.sanctuary-events.index', compact('events'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'nullable|date',
            'external_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
            'image' => admin_image_validation_rule(),
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        if ($request->hasFile('image')) {
            $data['image'] = store_optimized_image($request->file('image'), 'sanctuary-events');
        } else {
            unset($data['image']);
        }

        SanctuaryEvent::create($data);

        return response()->json(['success' => true, 'message' => 'Sanctuary event created successfully']);
    }

    public function show($id)
    {
        return response()->json(SanctuaryEvent::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'nullable|date',
            'external_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
            'image' => admin_image_validation_rule(),
        ]);

        $event = SanctuaryEvent::findOrFail($id);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? $event->sort_order);

        if ($request->hasFile('image')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $data['image'] = store_optimized_image($request->file('image'), 'sanctuary-events');
        } else {
            unset($data['image']);
        }

        $event->update($data);

        return response()->json(['success' => true, 'message' => 'Sanctuary event updated successfully']);
    }

    public function destroy($id)
    {
        $event = SanctuaryEvent::findOrFail($id);
        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }
        $event->delete();

        return response()->json(['success' => true, 'message' => 'Sanctuary event deleted successfully']);
    }
}
