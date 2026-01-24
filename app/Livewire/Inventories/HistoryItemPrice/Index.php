<?php

namespace App\Livewire\Inventories\HistoryItemPrice;

use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Item Price History')]
class Index extends Component
{
    #[On('cmw.inventories.history-item-price.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    public function render()
    {
        return view('livewire.inventories.history-item-price.index');
    }
}
