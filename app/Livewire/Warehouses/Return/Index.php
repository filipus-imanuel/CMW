<?php

namespace App\Livewire\Warehouses\Return;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Warehouse Return')]
class Index extends Component
{
    public function mount(): void
    {
        $this->authorize('view warehouse return');
    }

    public function render()
    {
        return view('livewire.warehouses.return.index');
    }
}
