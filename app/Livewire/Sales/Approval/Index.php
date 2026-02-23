<?php

namespace App\Livewire\Sales\Approval;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('SO Approval')]
class Index extends Component
{
    public function mount(): void
    {
        if (! Auth::user()?->can('view sales order') && ! Auth::user()?->can('approve sales order')) {
            abort(403);
        }
    }

    #[On('shp.sales.order.refresh.approval')]
    public function refresh(): void
    {
        // Triggers component refresh
    }

    public function render()
    {
        return view('livewire.sales.approval.index');
    }
}
