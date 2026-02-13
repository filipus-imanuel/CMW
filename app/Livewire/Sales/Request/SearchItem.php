<?php

namespace App\Livewire\Sales\Request;

use App\Helpers\CMW\PriceResolutionHelper;
use App\Models\CMW\Inventory\CategoryPrice;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Master\Partner;
use Livewire\Attributes\On;
use Livewire\Component;

class SearchItem extends Component
{
    public $search = '';

    public $itemCategoryId = null;

    public $partnerId = null;

    public $results = [];

    #[On('sales.request.search-item.open')]
    public function openModal(int $itemCategoryId, ?int $partnerId = null): void
    {
        $this->itemCategoryId = $itemCategoryId;
        $this->partnerId = $partnerId;
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

        $items = Item::query()
            ->where('is_active', true)
            ->when($this->itemCategoryId, fn ($q) => $q->where('item_category_id', $this->itemCategoryId))
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%");
            })
            ->with(['uom'])
            ->limit(20)
            ->get();

        $partner = $this->partnerId ? Partner::find($this->partnerId) : null;
        $resolvedPrices = PriceResolutionHelper::resolveMany($items, $partner);

        // Get unique category price IDs and load them
        $categoryPriceIds = collect($resolvedPrices)
            ->pluck('category_price_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $categoryPrices = CategoryPrice::whereIn('id', $categoryPriceIds)
            ->pluck('code', 'id')
            ->toArray();

        $this->results = $items
            ->map(function ($item) use ($resolvedPrices, $categoryPrices) {
                $resolved = $resolvedPrices[$item->id] ?? [];
                $categoryPriceId = $resolved['category_price_id'] ?? null;

                return [
                    'id' => $item->id,
                    'code' => $item->code,
                    'name' => $item->name,
                    'uom_id' => $item->uom_id,
                    'uom_name' => $item->uom?->name ?? '',
                    'het_price' => (float) $item->sell_price,
                    'sell_price' => $resolved['price'] ?? (float) $item->sell_price,
                    'price_source' => $resolved['source'] ?? 'item_sell_price',
                    'category_price_code' => $categoryPriceId ? ($categoryPrices[$categoryPriceId] ?? null) : null,
                ];
            })
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
                sellPrice: $item['sell_price'],
                hetPrice: $item['het_price']
            );

            $this->modal('search-item')->close();
        }
    }

    public function render()
    {
        return view('livewire.sales.request.search-item');
    }
}
