<?php

namespace App\Livewire\Sales\Return\Index;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Sales Return - Approval')]
class Approval extends Component
{
    public function mount(): void
    {
        if (! auth()->user()?->can('view sales return') && ! auth()->user()?->can('approve sales return')) {
            abort(403);
        }
    }

    public function render()
    {
        return view('livewire.sales.return.index.approval');
    }
}
