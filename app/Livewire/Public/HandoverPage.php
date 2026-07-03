<?php

namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontbase')]
class HandoverPage extends Component
{
    public function render()
    {
        return view('frontend.handover');
    }
}
