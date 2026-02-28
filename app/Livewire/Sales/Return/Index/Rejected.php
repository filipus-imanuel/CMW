<?php

namespace App\Livewire\Sales\Return\Index;

use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Sales Return - Rejected')]
class Rejected extends Component
{
    public function mount(): void
    {
        $this->authorize('view sales return');
    }

    #[On('shp.sales.return.refresh.rejected')]
    public function refresh(): void
    {
        // Triggers component refresh
    }

    public function render()
    {
        return view('livewire.sales.return.index.rejected');
    }
}
