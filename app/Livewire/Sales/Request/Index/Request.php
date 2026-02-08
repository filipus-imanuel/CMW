<?php

namespace App\Livewire\Sales\Request\Index;

use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Sales Requests - Approved')]
class Request extends Component
{
    public function mount(): void
    {
        $this->authorize('view sales request');
    }

    #[On('sales.request.refresh.request')]
    public function refresh(): void
    {
        // Triggers component refresh
    }

    public function render()
    {
        return view('livewire.sales.request.index.request');
    }
}
