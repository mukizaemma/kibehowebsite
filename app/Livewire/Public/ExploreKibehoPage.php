<?php

namespace App\Livewire\Public;

use App\Services\PublicWebsiteData;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontbase')]
class ExploreKibehoPage extends Component
{
    public function render()
    {
        $data = PublicWebsiteData::exploreKibeho();

        if (! $data['kibehoPage']->isActive()) {
            abort(404);
        }

        return view('frontend.explore-sanctuary', $data);
    }
}
