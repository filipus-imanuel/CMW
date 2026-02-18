<?php

namespace App\Livewire\Inventories\Item;

use App\Helpers\CMW\PopulateDataHelper;
use App\Models\CMW\History\HistoryItemPrice;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Inventory\ItemPrice;
use App\Models\CMW\Inventory\ItemUom;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Item')]
class Create extends Component
{
    public $inputs = [];

    /**
     * UOM rows: each has uom_id, conversion_rate, is_base, remarks,
     * and nested 'prices' array with category_price_id => price data.
     */
    public $uoms = [];

    public $dropdown_uom = [];

    public $dropdown_item_category = [];

    public $dropdown_category_prices = [];

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:items,code',
            'inputs.name' => 'required|string|max:100',
            'inputs.type' => 'required|in:RAW_MATERIAL,WORK_IN_PROCESS,FINISHED_GOOD,SPARE_PART',
            'inputs.item_category_id' => 'nullable|exists:item_categories,id',
            'inputs.cost_price' => 'required|numeric|min:0',
            'inputs.sell_price' => 'required|numeric|min:0',
            'inputs.min_stock' => 'nullable|numeric|min:0',
            'inputs.max_stock' => 'nullable|numeric|min:0',
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
            'uoms' => 'required|array|min:1',
            'uoms.*.uom_id' => 'required|exists:uoms,id',
            'uoms.*.conversion_rate' => 'required|numeric|min:0.0001',
            'uoms.*.is_base' => 'boolean',
            'uoms.*.remarks' => 'nullable|string|max:1024',
            'uoms.*.prices' => 'nullable|array',
            'uoms.*.prices.*.category_price_id' => 'required|exists:category_prices,id',
            'uoms.*.prices.*.price' => 'required|numeric|min:0',
            'uoms.*.prices.*.remarks' => 'nullable|string|max:1024',
            'uoms.*.prices.*.is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'inputs.code.required' => 'Code is required',
            'inputs.code.unique' => 'This code already exists',
            'inputs.name.required' => 'Name is required',
            'inputs.type.required' => 'Type is required',
            'inputs.cost_price.required' => 'Cost price is required',
            'inputs.sell_price.required' => 'Sell price is required',
            'uoms.required' => 'At least one UOM is required',
            'uoms.min' => 'At least one UOM is required',
            'uoms.*.uom_id.required' => 'UOM is required',
            'uoms.*.conversion_rate.required' => 'Conversion rate is required',
            'uoms.*.prices.*.price.required' => 'Price is required',
            'uoms.*.prices.*.price.min' => 'Price must be at least 0',
        ];
    }

    private function loadDropdowns(): void
    {
        $this->dropdown_uom = PopulateDataHelper::getUoms(['labelFormat' => 'name_code']);
        $this->dropdown_item_category = PopulateDataHelper::getItemCategories([
            'labelFormat' => 'name',
            'prependDefault' => true,
            'defaultLabel' => 'Select Category (Optional)',
        ]);
        $this->dropdown_category_prices = PopulateDataHelper::getCategoryPrices([
            'labelFormat' => 'name',
            'orderBy' => 'name',
            'orderDirection' => 'asc',
            'useCache' => false,
        ]);
    }

    public function mount(): void
    {
        $this->authorize('create item');

        $this->inputs = [
            'code' => '',
            'name' => '',
            'type' => 'FINISHED_GOOD',
            'item_category_id' => '',
            'cost_price' => 0,
            'sell_price' => 0,
            'min_stock' => 0,
            'max_stock' => 0,
            'remarks' => '',
            'is_active' => true,
        ];

        $this->loadDropdowns();

        // Auto-add one base UOM row with all category prices
        $this->addUomRow(true);
    }

    /**
     * Build prices array for a UOM row — one row per category price.
     */
    private function buildDefaultPrices(): array
    {
        return array_map(fn ($cp) => [
            'category_price_id' => $cp['value'],
            'category_price_label' => $cp['label'],
            'price' => 0,
            'remarks' => '',
            'is_active' => true,
        ], $this->dropdown_category_prices);
    }

    public function addUomRow(bool $isBase = false): void
    {
        $this->uoms[] = [
            'uom_id' => $this->dropdown_uom[0]['value'] ?? '',
            'conversion_rate' => $isBase ? 1.0000 : 1.0000,
            'is_base' => $isBase,
            'remarks' => '',
            'prices' => $this->buildDefaultPrices(),
        ];
    }

    public function removeUomRow(int $index): void
    {
        unset($this->uoms[$index]);
        $this->uoms = array_values($this->uoms);

        // Ensure at least one UOM remains as base
        if (! collect($this->uoms)->contains('is_base', true) && count($this->uoms) > 0) {
            $this->uoms[0]['is_base'] = true;
            $this->uoms[0]['conversion_rate'] = 1.0000;
        }
    }

    /**
     * When user sets a UOM as base, clear others.
     */
    public function setBaseUom(int $index): void
    {
        foreach ($this->uoms as $i => &$uom) {
            $uom['is_base'] = ($i === $index);
            if ($uom['is_base']) {
                $uom['conversion_rate'] = 1.0000;
            }
        }
        unset($uom);
    }

    public function store(): void
    {
        $this->authorize('create item');

        // Validate exactly one base UOM
        $baseCount = collect($this->uoms)->where('is_base', true)->count();
        if ($baseCount !== 1) {
            Flux::toast('Exactly one UOM must be marked as base', variant: 'danger', position: 'top right');

            return;
        }

        // Validate no duplicate UOMs
        $uomIds = array_column($this->uoms, 'uom_id');
        if (count($uomIds) !== count(array_unique($uomIds))) {
            Flux::toast('Duplicate UOMs are not allowed', variant: 'danger', position: 'top right');

            return;
        }

        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            $item = Item::create([
                ...$validated['inputs'],
                'created_by' => Auth::id(),
            ]);

            // Create item UOMs and their prices
            foreach ($validated['uoms'] as $uomData) {
                $itemUom = ItemUom::create([
                    'item_id' => $item->id,
                    'uom_id' => $uomData['uom_id'],
                    'conversion_rate' => $uomData['conversion_rate'],
                    'is_base' => $uomData['is_base'],
                    'remarks' => $uomData['remarks'] ?? null,
                    'created_by' => Auth::id(),
                ]);

                // Create prices for this UOM
                foreach ($uomData['prices'] ?? [] as $priceData) {
                    if (empty($priceData['price']) && (float) $priceData['price'] === 0.0) {
                        // Skip zero-price entries on create
                        continue;
                    }

                    // Check for soft-deleted duplicate — restore instead of creating new
                    $trashedRecord = ItemPrice::onlyTrashed()
                        ->where('item_uom_id', $itemUom->id)
                        ->where('category_price_id', $priceData['category_price_id'])
                        ->first();

                    if ($trashedRecord) {
                        $trashedRecord->restore();
                        $trashedRecord->update([
                            'price' => $priceData['price'],
                            'remarks' => $priceData['remarks'],
                            'is_active' => $priceData['is_active'],
                            'updated_by' => Auth::id(),
                            'deleted_by' => null,
                        ]);
                    } else {
                        ItemPrice::create([
                            'item_uom_id' => $itemUom->id,
                            'category_price_id' => $priceData['category_price_id'],
                            'price' => $priceData['price'],
                            'remarks' => $priceData['remarks'],
                            'is_active' => $priceData['is_active'],
                            'created_by' => Auth::id(),
                        ]);
                    }

                    // Log price history
                    HistoryItemPrice::create([
                        'item_uom_id' => $itemUom->id,
                        'category_price_id' => $priceData['category_price_id'],
                        'old_price' => 0,
                        'new_price' => $priceData['price'],
                        'created_by' => Auth::id(),
                    ]);
                }
            }
        });

        Flux::toast('Item created successfully', variant: 'success', position: 'top right');
        $this->redirectRoute('inventories.items.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.inventories.item.create');
    }
}
