<?php

namespace App\Livewire\Warehouses\Delivery\Ongoing;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Ongoing Delivery - Sales Orders')]
class So extends Component
{
    public function mount(): void
    {
        $this->authorize('view delivery order');
    }

    public function render()
    {
        return view('livewire.warehouses.delivery.ongoing.so');
    }
}
