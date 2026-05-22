<?php

namespace App\Livewire\Production;

use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Production - Ongoing')]
class Index extends Component
{
    public function mount(): void
    {
        $this->authorize('view production order');
    }

    #[On('shp.production.order.refresh')]
    public function refresh(): void
    {
        // Triggers component refresh
    }

    public function render()
    {
        return view('livewire.production.index');
    }
}
