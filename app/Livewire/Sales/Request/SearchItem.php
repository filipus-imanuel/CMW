<?php

namespace App\Livewire\Sales\Request;

use App\Models\CMW\Inventory\Item;
use Livewire\Attributes\On;
use Livewire\Component;

class SearchItem extends Component
{
    public $search = '';

    public $categoryId = null;

    public $results = [];

    #[On('sales.request.search-item.open')]
    public function openModal(int $categoryId): void
    {
        $this->categoryId = $categoryId;
        $this->search = '';
        $this->results = [];
        $this->modal('search-item')->show();
    }

    public function updatedSearch(): void
    {
        $this->searchItems();
    }

    public function searchItems(): void
    {
        if (strlen($this->search) < 2) {
            $this->results = [];

            return;
        }

        $this->results = Item::query()
            ->where('is_active', true)
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%");
            })
            ->with(['uom'])
            ->limit(20)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'uom_id' => $item->uom_id,
                'uom_name' => $item->uom?->name ?? '',
                'sell_price' => (float) $item->sell_price,
            ])
            ->toArray();
    }

    public function selectItem(int $itemId): void
    {
        $item = collect($this->results)->firstWhere('id', $itemId);

        if ($item) {
            $this->dispatch('sales.request.item-selected',
                itemId: $item['id'],
                itemCode: $item['code'],
                itemName: $item['name'],
                uomId: $item['uom_id'],
                uomName: $item['uom_name'],
                sellPrice: $item['sell_price']
            );

            $this->modal('search-item')->close();
        }
    }

    public function render()
    {
        return view('livewire.sales.request.search-item');
    }
}
