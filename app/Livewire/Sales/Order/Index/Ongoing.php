<?php

namespace App\Livewire\Sales\Order\Index;

use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Ongoing Orders')]
class Ongoing extends Component
{
    public function mount(): void
    {
        $this->authorize('view sales order');
    }

    #[On('shp.sales.order.refresh.ongoing')]
    public function refresh(): void
    {
        // Triggers component refresh
    }

    public function render()
    {
        return view('livewire.sales.order.index.ongoing');
    }
}
