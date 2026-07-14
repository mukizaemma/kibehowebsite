<?php

namespace App\Http\Controllers;

use App\Models\WhyChooseUsItem;
use Illuminate\Http\Request;

class WhyChooseUsController extends Controller
{
    public function index()
    {
        $items = WhyChooseUsItem::query()->orderBy('sort_order')->orderBy('id')->get();

        return view('content-management.why-choose-us.index', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['icon'] = $this->normalizeIcon($data['icon'] ?? null);

        WhyChooseUsItem::create($data);

        return response()->json(['success' => true, 'message' => 'Item created successfully']);
    }

    public function show($id)
    {
        $item = WhyChooseUsItem::findOrFail($id);

        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);

        $item = WhyChooseUsItem::findOrFail($id);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['icon'] = $this->normalizeIcon($data['icon'] ?? null);
        $item->update($data);

        return response()->json(['success' => true, 'message' => 'Item updated successfully']);
    }

    public function destroy($id)
    {
        WhyChooseUsItem::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Item deleted successfully']);
    }

    private function normalizeIcon(?string $icon): string
    {
        $icon = trim((string) $icon);
        if ($icon === '') {
            return 'fa-solid fa-circle-dot';
        }
        if (! str_contains($icon, ' ')) {
            return 'fa-solid '.$icon;
        }

        return $icon;
    }
}
