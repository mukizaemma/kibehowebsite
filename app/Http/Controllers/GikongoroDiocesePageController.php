<?php

namespace App\Http\Controllers;

use App\Models\GikongoroDiocesePage;
use App\Models\GikongoroDioceseStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GikongoroDiocesePageController extends Controller
{
    public function index()
    {
        $page = GikongoroDiocesePage::current();
        $stats = GikongoroDioceseStat::query()->ordered()->get();

        return view('content-management.gikongoro-diocese.index', compact('page', 'stats'));
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
            'header_image' => admin_image_validation_rule(),
            'profile_image' => admin_image_validation_rule(),
            'stats_background_image' => admin_image_validation_rule(),
            'status' => 'required|in:Active,Inactive',
        ]);

        $page = GikongoroDiocesePage::current();
        $page->title = $data['title'];
        $page->description = $data['description'] ?? null;
        $page->official_website_url = $data['official_website_url'] ?? null;
        $page->status = $data['status'];

        foreach (['header_image', 'profile_image', 'stats_background_image'] as $field) {
            if ($request->hasFile($field)) {
                if ($page->{$field}) {
                    Storage::disk('public')->delete($page->{$field});
                }
                $page->{$field} = store_optimized_image($request->file($field), 'gikongoro-diocese/'.$field);
            }
        }

        $page->save();

        return response()->json(['success' => true, 'message' => 'Gikongoro Diocese page updated successfully']);
    }

    public function storeStat(Request $request)
    {
        $request->merge([
            'sort_order' => $this->nullableSortOrder($request->input('sort_order')),
        ]);

        $data = $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['icon'] = filled($data['icon'] ?? null) ? $data['icon'] : 'fa-solid fa-circle-dot';

        GikongoroDioceseStat::create($data);

        return response()->json(['success' => true, 'message' => 'Statistic created successfully']);
    }

    public function showStat($id)
    {
        return response()->json(GikongoroDioceseStat::findOrFail($id));
    }

    public function updateStat(Request $request, $id)
    {
        $request->merge([
            'sort_order' => $this->nullableSortOrder($request->input('sort_order')),
        ]);

        $data = $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
        ]);

        $stat = GikongoroDioceseStat::findOrFail($id);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? $stat->sort_order);

        $stat->update($data);

        return response()->json(['success' => true, 'message' => 'Statistic updated successfully']);
    }

    public function destroyStat($id)
    {
        GikongoroDioceseStat::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Statistic deleted successfully']);
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
