<?php

namespace App\Livewire\Sales\Request\Approval;

use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Sales Request Approvals')]
class Index extends Component
{
    public function mount(): void
    {
        $this->authorize('approve sales request');
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
