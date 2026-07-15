<?php

namespace App\Livewire\Public;

use App\Services\PublicWebsiteData;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontbase')]
class SanctuaryActivityShowPage extends Component
{
    public string $slug;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    public function render()
    {
        return view('frontend.destination-activity', PublicWebsiteData::sanctuaryActivity($this->slug));
    }
}
