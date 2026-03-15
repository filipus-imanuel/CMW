<?php

namespace App\Livewire\Sales\Invoice\Index;

use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Paid Invoices')]
class Paid extends Component
{
    public function mount(): void
    {
        $this->authorize('view ar invoice');
    }

    #[On('shp.sales.invoice.refresh.paid')]
    public function refresh(): void
    {
        // Triggers component refresh
    }

    public function render()
    {
        return view('livewire.sales.invoice.index.paid');
    }
}
