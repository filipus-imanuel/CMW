<?php

namespace App\Livewire\Sales\Return\Index;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Sales Return - Cancelled')]
class Cancelled extends Component
{
    public function mount(): void
    {
        $this->authorize('view sales return');
    }

    public function render()
    {
        return view('livewire.sales.return.index.cancelled');
    }
}
