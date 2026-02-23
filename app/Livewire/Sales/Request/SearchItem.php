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

    public array $addedUomIds = [];

    #[On('sales.request.search-item.open')]
    public function openModal(int $itemCategoryId, ?int $partnerId = null): void
    {
        $this->itemCategoryId = $itemCategoryId;
        $this->partnerId = $partnerId;
        $this->search = '';
        $this->results = [];
        $this->addedUomIds = [];
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
            ->with(['itemUoms.uom'])
            ->limit(20)
            ->get();

        $partner = $this->partnerId ? Partner::find($this->partnerId) : null;

        // Build one row per (item × uom)
        $rows = [];
        $categoryPriceIds = [];

        foreach ($items as $item) {
            foreach ($item->itemUoms as $itemUom) {
                $resolved = PriceResolutionHelper::resolve($item, $partner, $itemUom->id);
                $hetResolved = PriceResolutionHelper::resolve($item, null, $itemUom->id);

                if ($resolved['category_price_id']) {
                    $categoryPriceIds[] = $resolved['category_price_id'];
                }

                $rows[] = [
                    'id' => $item->id,
                    'code' => $item->code,
                    'name' => $item->name,
                    'item_uom_id' => $itemUom->id,
                    'uom_name' => $itemUom->uom?->name ?? '',
                    'is_base' => (bool) $itemUom->is_base,
                    'het_price' => $hetResolved['price'],
                    'sell_price' => $resolved['price'],
                    'price_source' => $resolved['source'],
                    'category_price_id' => $resolved['category_price_id'],
                    'category_price_code' => null,
                ];
            }
        }

        // Batch-load category price codes
        $categoryPriceCodes = CategoryPrice::whereIn('id', array_unique(array_filter($categoryPriceIds)))
            ->pluck('code', 'id')
            ->toArray();

        $this->results = array_map(function ($row) use ($categoryPriceCodes) {
            $row['category_price_code'] = $row['category_price_id']
                ? ($categoryPriceCodes[$row['category_price_id']] ?? null)
                : null;
            unset($row['category_price_id']);

            return $row;
        }, $rows);
    }

    public function selectItem(int $itemUomId): void
    {
        $item = collect($this->results)->firstWhere('item_uom_id', $itemUomId);

        if ($item) {
            $this->dispatch('sales.request.item-selected',
                itemId: $item['id'],
                itemCode: $item['code'],
                itemName: $item['name'],
                itemUomId: $item['item_uom_id'],
                uomName: $item['uom_name'],
                sellPrice: $item['sell_price'],
                hetPrice: $item['het_price']
            );

            $this->addedUomIds[] = $itemUomId;
        }
    }

    public function render()
    {
        return view('livewire.sales.request.search-item');
    }
}
