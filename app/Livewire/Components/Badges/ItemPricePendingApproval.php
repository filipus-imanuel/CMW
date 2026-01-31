<?php

namespace App\Livewire\Components\Badges;

use App\Models\CMW\Inventory\PendingItemPrice;
use Livewire\Attributes\On;
use Livewire\Component;

class ItemPricePendingApproval extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->refreshCount();
    }

    /**
     * Refresh the pending count.
     * Listens to both Livewire event and browser event for cross-component updates.
     */
    #[On('item-price-approval.badge-refresh')]
    public function refreshCount(): void
    {
        $this->count = PendingItemPrice::where('status', 'pending')->count();
    }

    public function render()
    {
        return view('livewire.components.badges.item-price-pending-approval');
    }
}
