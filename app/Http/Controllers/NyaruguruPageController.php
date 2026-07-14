<?php

namespace App\Http\Controllers;

use App\Models\NyaruguruActivity;
use App\Models\NyaruguruPage;
use App\Models\NyaruguruPageImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NyaruguruPageController extends Controller
{
    public function index()
    {
        $page = NyaruguruPage::current()->load('images');
        $activities = NyaruguruActivity::query()->ordered()->get();

        return view('content-management.nyaruguru-page.index', compact('page', 'activities'));
    }

    public function updatePage(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'home_title' => 'nullable|string|max:255',
            'home_lead' => 'nullable|string|max:2000',
            'cover_image' => admin_image_validation_rule(),
            'status' => 'required|in:Active,Inactive',
            'images.*' => admin_image_validation_rule(),
        ]);

        $page = NyaruguruPage::current();
        $page->title = $data['title'];
        $page->description = $data['description'] ?? null;
        $page->home_title = $data['home_title'] ?? null;
        $page->home_lead = $data['home_lead'] ?? null;
        $page->status = $data['status'];

        if ($request->hasFile('cover_image')) {
            if ($page->cover_image) {
                Storage::disk('public')->delete($page->cover_image);
            }
            $page->cover_image = store_optimized_image($request->file('cover_image'), 'nyaruguru-page');
        }

        $page->save();

        if ($request->hasFile('images')) {
            $sort = (int) $page->images()->max('sort_order');
            foreach ($request->file('images') as $image) {
                $sort++;
                NyaruguruPageImage::create([
                    'nyaruguru_page_id' => $page->id,
                    'image' => store_optimized_image($image, 'nyaruguru-page/gallery'),
                    'sort_order' => $sort,
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Nyaruguru page updated successfully']);
    }

    public function deleteImage($id)
    {
        $image = NyaruguruPageImage::findOrFail($id);
        Storage::disk('public')->delete($image->image);
        $image->delete();

        return response()->json(['success' => true, 'message' => 'Image removed successfully']);
    }

    public function storeActivity(Request $request)
    {
        $request->merge([
            'external_url' => $this->nullableUrl($request->input('external_url')),
            'sort_order' => $this->nullableSortOrder($request->input('sort_order')),
        ]);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'external_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
            'image' => admin_image_validation_rule(),
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        if ($request->hasFile('image')) {
            $data['image'] = store_optimized_image($request->file('image'), 'nyaruguru-page/activities');
        } else {
            unset($data['image']);
        }

        NyaruguruActivity::create($data);

        return response()->json(['success' => true, 'message' => 'Activity created successfully']);
    }

    public function showActivity($id)
    {
        return response()->json(NyaruguruActivity::findOrFail($id));
    }

    public function updateActivity(Request $request, $id)
    {
        $request->merge([
            'external_url' => $this->nullableUrl($request->input('external_url')),
            'sort_order' => $this->nullableSortOrder($request->input('sort_order')),
        ]);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'external_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
            'image' => admin_image_validation_rule(),
        ]);

        $activity = NyaruguruActivity::findOrFail($id);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? $activity->sort_order);

        if ($request->hasFile('image')) {
            if ($activity->image) {
                Storage::disk('public')->delete($activity->image);
            }
            $data['image'] = store_optimized_image($request->file('image'), 'nyaruguru-page/activities');
        } else {
            unset($data['image']);
        }

        $activity->update($data);

        return response()->json(['success' => true, 'message' => 'Activity updated successfully']);
    }

    public function destroyActivity($id)
    {
        $activity = NyaruguruActivity::findOrFail($id);
        if ($activity->image) {
            Storage::disk('public')->delete($activity->image);
        }
        $activity->delete();

        return response()->json(['success' => true, 'message' => 'Activity deleted successfully']);
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
