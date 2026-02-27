<?php

namespace App\Livewire\Warehouses\Delivery\Upcoming;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Upcoming Sales Orders')]
class Index extends Component
{
    public function mount(): void
    {
        $this->authorize('view delivery order');
    }

    public function render()
    {
        return view('livewire.warehouses.delivery.upcoming.index');
    }
}
