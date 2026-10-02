<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class VillageLinkLayout extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $header = null,
    ) {}

    public function render(): View
    {
        return view('layouts.villagelink');
    }
}
