<?php

namespace App\Livewire\Inventories\Item;

use App\Helpers\CMW\PopulateDataHelper;
use App\Models\CMW\History\HistoryItemPrice;
use App\Models\CMW\Inventory\InventoryLedger;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Inventory\ItemPrice;
use App\Models\CMW\Inventory\ItemUom;
use App\Models\CMW\Inventory\ItemWarehouse;
use App\Models\CMW\Master\Warehouse;
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

    /** Warehouse assignment — flat array of warehouse IDs (checkbox-driven). */
    public $item_warehouses = [];

    /** Initial stock qty per warehouse — keyed by warehouse ID: [warehouseId => ['qty' => 0]]. */
    public $initial_stocks = [];

    public int $baseUomIndex = 0;

    public $dropdown_uom = [];

    public $dropdown_item_category = [];

    public $dropdown_category_prices = [];

    public $dropdown_warehouses = [];

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:items,code',
            'inputs.name' => 'required|string|max:100',
            'inputs.type' => 'required|in:RAW_MATERIAL,WORK_IN_PROCESS,FINISHED_GOOD,SPARE_PART',
            'inputs.item_category_id' => 'required|exists:item_categories,id',
            'inputs.cost_price' => 'required|numeric|min:0',
            'inputs.sell_price' => 'required|numeric|min:0',
            'inputs.min_stock' => 'nullable|numeric|min:0',
            'inputs.max_stock' => 'nullable|numeric|min:0',
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
            'inputs.default_warehouse_id' => 'nullable|exists:warehouses,id',
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
            'item_warehouses' => 'nullable|array',
            'item_warehouses.*' => 'exists:warehouses,id',
            'initial_stocks' => 'nullable|array',
            'initial_stocks.*.qty' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'inputs.code.required' => 'Code is required',
            'inputs.code.unique' => 'This code already exists',
            'inputs.name.required' => 'Name is required',
            'inputs.type.required' => 'Type is required',
            'inputs.item_category_id.required' => 'Category is required',
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
        ]);
        $this->dropdown_category_prices = PopulateDataHelper::getCategoryPrices([
            'labelFormat' => 'name',
            'orderBy' => 'name',
            'orderDirection' => 'asc',
            'useCache' => false,
        ]);
        $this->dropdown_warehouses = Warehouse::with('companies')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($w) => [
                'value' => $w->id,
                'label' => $w->name,
                'company_name' => $w->companies->pluck('name')->join(', ') ?: '—',
            ])
            ->toArray();
    }

    public function mount(): void
    {
        $this->authorize('create item');

        $this->inputs = [
            'code' => '',
            'name' => '',
            'type' => 'FINISHED_GOOD',
            'item_category_id' => '',
            'default_warehouse_id' => '',
            'cost_price' => 0,
            'sell_price' => 0,
            'min_stock' => 0,
            'max_stock' => 0,
            'remarks' => '',
            'is_active' => true,
        ];

        $this->item_warehouses = [];
        $this->initial_stocks = [];

        $this->loadDropdowns();

        // Auto-add one base UOM row with all category prices
        $this->addUomRow(true);
        $this->baseUomIndex = 0;
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
            $this->baseUomIndex = 0;
        } else {
            // Re-sync baseUomIndex after re-indexing
            $this->baseUomIndex = collect($this->uoms)->search(fn ($u) => $u['is_base']) ?? 0;
        }
    }

    /**
     * Sync initial_stocks when warehouse checkboxes change.
     */
    public function updatedItemWarehouses(): void
    {
        $currentIds = collect($this->item_warehouses)->map(fn ($v) => (int) $v)->toArray();

        // Add entries for newly-checked warehouses
        foreach ($currentIds as $whId) {
            if (! isset($this->initial_stocks[$whId])) {
                $this->initial_stocks[$whId] = ['qty' => 0];
            }
        }

        // Remove entries for unchecked warehouses
        foreach (array_keys($this->initial_stocks) as $existingId) {
            if (! in_array((int) $existingId, $currentIds)) {
                unset($this->initial_stocks[$existingId]);
            }
        }

        // Clear default warehouse if it was unchecked
        $defaultWarehouseId = $this->inputs['default_warehouse_id'] ?? '';
        if ($defaultWarehouseId && ! in_array((int) $defaultWarehouseId, $currentIds)) {
            $this->inputs['default_warehouse_id'] = '';
        }
    }

    /**
     * Get warehouse IDs currently assigned (for default warehouse dropdown filtering).
     */
    public function getAssignedWarehouseIdsProperty(): array
    {
        return collect($this->item_warehouses)
            ->map(fn ($v) => (int) $v)
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Get the label of the base UOM for display in initial stock inputs.
     */
    public function getBaseUomLabelProperty(): string
    {
        $baseRow = collect($this->uoms)->firstWhere('is_base', true);
        if (! $baseRow) {
            return '';
        }

        $uom = collect($this->dropdown_uom)->firstWhere('value', $baseRow['uom_id']);

        return $uom['label'] ?? '';
    }

    /**
     * When user sets a UOM as base, clear others.
     */
    public function setBaseUom(int $index): void
    {
        $this->baseUomIndex = $index;
        foreach ($this->uoms as $i => &$uom) {
            $uom['is_base'] = ($i === $index);
            if ($uom['is_base']) {
                $uom['conversion_rate'] = 1.0000;
            }
        }
        unset($uom);
    }

    public function updatedBaseUomIndex(int $value): void
    {
        $this->setBaseUom($value);
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

        // Validate default warehouse is in assigned warehouses
        $warehouseIds = array_map('intval', array_filter($this->item_warehouses));
        $defaultWarehouseId = $this->inputs['default_warehouse_id'] ?? '';
        if ($defaultWarehouseId && ! in_array((int) $defaultWarehouseId, $warehouseIds)) {
            Flux::toast('Default warehouse must be one of the assigned warehouses', variant: 'danger', position: 'top right');

            return;
        }

        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            // Prepare inputs — clear empty default_warehouse_id
            $itemInputs = $validated['inputs'];
            if (empty($itemInputs['default_warehouse_id'])) {
                $itemInputs['default_warehouse_id'] = null;
            }

            $item = Item::create([
                ...$itemInputs,
                'created_by' => Auth::id(),
            ]);

            // Create warehouse assignments
            foreach ($validated['item_warehouses'] ?? [] as $warehouseId) {
                if (! empty($warehouseId)) {
                    ItemWarehouse::create([
                        'item_id' => $item->id,
                        'warehouse_id' => $warehouseId,
                        'created_by' => Auth::id(),
                    ]);
                }
            }

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

            // Write initial stock ledger entries
            foreach ($this->initial_stocks as $warehouseId => $stockData) {
                $qty = (float) ($stockData['qty'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                // Only write for warehouses that are actually assigned
                if (! in_array((int) $warehouseId, array_map('intval', $validated['item_warehouses'] ?? []))) {
                    continue;
                }

                $lastBalance = InventoryLedger::where('item_id', $item->id)
                    ->where('warehouse_id', $warehouseId)
                    ->lockForUpdate()
                    ->orderByDesc('id')
                    ->value('balance') ?? 0;

                InventoryLedger::create([
                    'item_id' => $item->id,
                    'warehouse_id' => $warehouseId,
                    'date' => now()->toDateString(),
                    'type' => 'initial_stock',
                    'reference_type' => Item::class,
                    'reference_id' => $item->id,
                    'quantity_in' => $qty,
                    'quantity_out' => 0,
                    'balance' => $lastBalance + $qty,
                    'remarks' => 'Initial stock on item creation',
                    'created_by' => Auth::id(),
                ]);
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
