<?php

namespace App\Livewire\Sales\Return\Index;

use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Sales Return - Draft')]
class Draft extends Component
{
    public function mount(): void
    {
        $this->authorize('view sales return');
    }

    #[On('sales.return.refresh.draft')]
    public function refresh(): void {}

    public function render()
    {
        return view('livewire.sales.return.index.draft');
    }
}
