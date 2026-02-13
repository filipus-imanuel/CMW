<?php

namespace App\Livewire\Sales\Request\Approval;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Sales Request Approvals')]
class Index extends Component
{
    public function mount(): void
    {
        // Allow both view and approve permissions
        if (! Auth::user()?->can('view sales request') && ! Auth::user()?->can('approve sales request')) {
            abort(403);
        }
    }

    #[On('sales.request.refresh.approval')]
    public function refresh(): void
    {
        // Triggers component refresh
    }

    public function render()
    {
        return view('livewire.sales.request.approval.index');
    }
}
