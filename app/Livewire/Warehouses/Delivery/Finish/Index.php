<?php

namespace App\Livewire\Warehouses\Delivery\Finish;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Finished Deliveries')]
class Index extends Component
{
    public function mount(): void
    {
        $this->authorize('view delivery order');
    }

    public function render()
    {
        return view('livewire.warehouses.delivery.finish.index');
    }
}
