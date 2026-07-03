<?php

namespace App\Http\Controllers;

use App\Services\SiteTranslationService;
use App\Support\SiteLocale;
use Illuminate\Http\Request;

class SiteTranslationController extends Controller
{
    public function __construct(
        private readonly SiteTranslationService $translations
    ) {}

    public function index(Request $request)
    {
        $group = $request->query('group');
        $search = $request->query('search');

        return view('admin.site-translations.index', [
            'rows' => $this->translations->adminRows(
                is_string($group) && $group !== '' ? $group : null,
                is_string($search) && $search !== '' ? $search : null,
            ),
            'groups' => $this->translations->groups(),
            'group' => $group,
            'search' => $search,
            'missingFrench' => $this->translations->missingFrenchCount(),
            'translationsEnabled' => SiteLocale::translationsEnabled(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:191',
            'en_value' => 'nullable|string|max:5000',
            'fr_value' => 'nullable|string|max:5000',
        ]);

        $this->translations->setOverride(
            $validated['key'],
            SiteLocale::DEFAULT,
            $validated['en_value'] ?? null
        );

        $this->translations->setOverride(
            $validated['key'],
            SiteLocale::FRENCH,
            $validated['fr_value'] ?? null
        );

        return redirect()
            ->route('content-management.site-translations.index', $request->only(['group', 'search']))
            ->with('success', 'Translation saved.');
    }

    public function reset(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:191',
            'locale' => 'required|in:en,fr',
        ]);

        $this->translations->setOverride($validated['key'], $validated['locale'], null);

        return redirect()
            ->route('content-management.site-translations.index', $request->only(['group', 'search']))
            ->with('success', 'Translation reset to file default.');
    }
}
