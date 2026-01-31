<?php

namespace App\Livewire\Inventories\ItemPrice;

use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Item Price Approval History')]
class ApprovalHistory extends Component
{
    public function mount(): void
    {
        $this->authorize('view item price approval');
    }

    #[On('cmw.inventories.item-price.approval-history.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    public function render()
    {
        return view('livewire.inventories.item-price.approval-history');
    }
}
