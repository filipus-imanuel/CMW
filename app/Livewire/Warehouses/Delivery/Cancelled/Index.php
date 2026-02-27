<?php

namespace App\Livewire\Warehouses\Delivery\Cancelled;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Cancelled Deliveries')]
class Index extends Component
{
    public function mount(): void
    {
        $this->authorize('view delivery order');
    }

    public function render()
    {
        return view('livewire.warehouses.delivery.cancelled.index');
    }
}
