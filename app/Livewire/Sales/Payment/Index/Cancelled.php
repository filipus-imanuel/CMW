<?php

namespace App\Livewire\Sales\Payment\Index;

use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Cancelled Payments')]
class Cancelled extends Component
{
    public function mount(): void
    {
        $this->authorize('view ar payment');
    }

    #[On('shp.sales.payment.refresh.cancelled')]
    public function refresh(): void
    {
        // Triggers component refresh
    }

    public function render()
    {
        return view('livewire.sales.payment.index.cancelled');
    }
}
