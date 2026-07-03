<?php

namespace App\Livewire\Public;

use App\Services\PublicWebsiteData;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontbase')]
class DiscoverGikongoroDiocesePage extends Component
{
    public function render()
    {
        $data = PublicWebsiteData::gikongoroDiocese();

        if (! $data['diocesePage']->isActive()) {
            abort(404);
        }

        return view('frontend.discover-gikongoro-diocese', $data);
    }
}
