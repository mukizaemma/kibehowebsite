<?php

namespace App\Livewire\Public;

use App\Services\PublicWebsiteData;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontbase')]
class DiscoverNyaruguruPage extends Component
{
    public function render()
    {
        $data = PublicWebsiteData::discoverNyaruguru();

        if (! $data['nyaruguruPage']->isActive()) {
            abort(404);
        }

        return view('frontend.discover-nyaruguru', $data);
    }
}
