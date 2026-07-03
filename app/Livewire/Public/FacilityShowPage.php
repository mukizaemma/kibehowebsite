<?php

namespace App\Livewire\Public;

use App\Services\PublicWebsiteData;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontbase')]
class FacilityShowPage extends Component
{
    public string $slug;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    public function render()
    {
        if ($this->slug === \App\Models\Facility::EXPLORE_KIBEHO_SLUG) {
            return view('frontend.explore-sanctuary', PublicWebsiteData::exploreSanctuary());
        }

        return view('frontend.facility', PublicWebsiteData::facility($this->slug));
    }
}
