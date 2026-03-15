<?php

namespace App\Livewire\Sales\Invoice\Index;

use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Unpaid Invoices')]
class Unpaid extends Component
{
    public function mount(): void
    {
        $this->authorize('view ar invoice');
    }

    #[On('shp.sales.invoice.refresh.unpaid')]
    public function refresh(): void
    {
        // Triggers component refresh
    }

    public function render()
    {
        return view('livewire.sales.invoice.index.unpaid');
    }
}
