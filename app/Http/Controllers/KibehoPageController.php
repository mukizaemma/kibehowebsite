<?php

namespace App\Http\Controllers;

use App\Models\KibehoPage;
use App\Models\KibehoPageImage;
use App\Models\SanctuaryEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KibehoPageController extends Controller
{
    public function index()
    {
        $page = KibehoPage::current()->load('images');
        $events = SanctuaryEvent::query()->ordered()->get();

        return view('content-management.kibeho-page.index', compact('page', 'events'));
    }

    public function updatePage(Request $request)
    {
        $request->merge([
            'official_website_url' => $this->nullableUrl($request->input('official_website_url')),
        ]);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'official_website_url' => 'nullable|url|max:500',
            'cover_image' => admin_image_validation_rule(),
            'status' => 'required|in:Active,Inactive',
            'images.*' => admin_image_validation_rule(),
        ]);

        $page = KibehoPage::current();
        $page->title = $data['title'];
        $page->description = $data['description'] ?? null;
        $page->official_website_url = $data['official_website_url'] ?? null;
        $page->status = $data['status'];

        if ($request->hasFile('cover_image')) {
            if ($page->cover_image) {
                Storage::disk('public')->delete($page->cover_image);
            }
            $page->cover_image = store_optimized_image($request->file('cover_image'), 'kibeho-page');
        }

        $page->save();

        if ($request->hasFile('images')) {
            $sort = (int) $page->images()->max('sort_order');
            foreach ($request->file('images') as $image) {
                $sort++;
                KibehoPageImage::create([
                    'kibeho_page_id' => $page->id,
                    'image' => store_optimized_image($image, 'kibeho-page/gallery'),
                    'sort_order' => $sort,
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Kibeho page updated successfully']);
    }

    public function deleteImage($id)
    {
        $image = KibehoPageImage::findOrFail($id);
        Storage::disk('public')->delete($image->image);
        $image->delete();

        return response()->json(['success' => true, 'message' => 'Image removed successfully']);
    }

    public function storeEvent(Request $request)
    {
        $request->merge([
            'external_url' => $this->nullableUrl($request->input('external_url')),
            'sort_order' => $this->nullableSortOrder($request->input('sort_order')),
        ]);

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
            $data['image'] = store_optimized_image($request->file('image'), 'kibeho-page/events');
        } else {
            unset($data['image']);
        }

        SanctuaryEvent::create($data);

        return response()->json(['success' => true, 'message' => 'Event created successfully']);
    }

    public function showEvent($id)
    {
        return response()->json(SanctuaryEvent::findOrFail($id));
    }

    public function updateEvent(Request $request, $id)
    {
        $request->merge([
            'external_url' => $this->nullableUrl($request->input('external_url')),
            'sort_order' => $this->nullableSortOrder($request->input('sort_order')),
        ]);

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
            $data['image'] = store_optimized_image($request->file('image'), 'kibeho-page/events');
        } else {
            unset($data['image']);
        }

        $event->update($data);

        return response()->json(['success' => true, 'message' => 'Event updated successfully']);
    }

    public function destroyEvent($id)
    {
        $event = SanctuaryEvent::findOrFail($id);
        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }
        $event->delete();

        return response()->json(['success' => true, 'message' => 'Event deleted successfully']);
    }

    private function nullableUrl(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || $value === 'https://' || $value === 'http://') {
            return null;
        }

        return $value;
    }

    private function nullableSortOrder(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return is_numeric($value) ? (int) $value : null;
    }
}
