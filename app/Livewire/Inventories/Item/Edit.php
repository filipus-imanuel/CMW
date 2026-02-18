<?php

namespace App\Livewire\Inventories\Item;

use App\Helpers\CMW\PopulateDataHelper;
use App\Models\CMW\History\HistoryItemPrice;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Inventory\ItemPrice;
use App\Models\CMW\Inventory\ItemUom;
use App\Models\CMW\Inventory\PendingItemPrice;
use App\Models\CMW\System\Setting;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Item')]
class Edit extends Component
{
    public ?Item $item = null;

    public $inputs = [];

    /**
     * UOM rows: each has id (existing ItemUom ID or null), uom_id, conversion_rate,
     * is_base, remarks, and nested 'prices' array.
     */
    public $uoms = [];

    public $removedUomIds = [];

    public $dropdown_uom = [];

    public $dropdown_item_category = [];

    public $dropdown_category_prices = [];

    /**
     * Get the approval threshold from system settings.
     */
    #[Computed]
    public function threshold(): float
    {
        return Setting::get('inventory.item_price.threshold_bypass_approval', 0);
    }

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:items,code,'.$this->item?->id,
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

    /**
     * Build prices array for a UOM row — one row per category price.
     */
    private function buildDefaultPrices(): array
    {
        return array_map(fn ($cp) => [
            'id' => null,
            'category_price_id' => $cp['value'],
            'category_price_label' => $cp['label'],
            'price' => 0,
            'original_price' => 0,
            'remarks' => '',
            'is_active' => true,
        ], $this->dropdown_category_prices);
    }

    public function mount($id): void
    {
        $this->authorize('edit item');

        $this->item = Item::with(['itemUoms.uom', 'itemUoms.itemPrices.categoryPrice'])->findOrFail($id);

        $this->inputs = [
            'code' => $this->item->code,
            'name' => $this->item->name,
            'type' => $this->item->type,
            'item_category_id' => $this->item->item_category_id ?? '',
            'cost_price' => $this->item->cost_price,
            'sell_price' => $this->item->sell_price,
            'min_stock' => $this->item->min_stock,
            'max_stock' => $this->item->max_stock,
            'remarks' => $this->item->remarks ?? '',
            'is_active' => (bool) $this->item->is_active,
        ];

        $this->loadDropdowns();

        // Populate existing UOMs with their prices
        $this->uoms = $this->item->itemUoms->map(function ($itemUom) {
            // Build prices: existing + missing categories
            $existingPrices = $itemUom->itemPrices->map(fn ($ip) => [
                'id' => $ip->id,
                'category_price_id' => $ip->category_price_id,
                'category_price_label' => $ip->categoryPrice ? ($ip->categoryPrice->code.' - '.$ip->categoryPrice->name) : '-',
                'price' => $ip->price,
                'original_price' => $ip->price,
                'remarks' => $ip->remarks ?? '',
                'is_active' => (bool) $ip->is_active,
            ])->toArray();

            // Auto-add rows for categories not yet assigned
            $existingCategoryIds = array_column($existingPrices, 'category_price_id');
            foreach ($this->dropdown_category_prices as $cp) {
                if (! in_array($cp['value'], $existingCategoryIds)) {
                    $existingPrices[] = [
                        'id' => null,
                        'category_price_id' => $cp['value'],
                        'category_price_label' => $cp['label'],
                        'price' => 0,
                        'original_price' => 0,
                        'remarks' => '',
                        'is_active' => true,
                    ];
                }
            }

            return [
                'id' => $itemUom->id,
                'uom_id' => $itemUom->uom_id,
                'uom_label' => $itemUom->uom?->name ?? '-',
                'conversion_rate' => $itemUom->conversion_rate,
                'is_base' => (bool) $itemUom->is_base,
                'remarks' => $itemUom->remarks ?? '',
                'prices' => $existingPrices,
            ];
        })->toArray();
    }

    public function addUomRow(): void
    {
        $this->uoms[] = [
            'id' => null,
            'uom_id' => '',
            'uom_label' => '',
            'conversion_rate' => 1.0000,
            'is_base' => false,
            'remarks' => '',
            'prices' => $this->buildDefaultPrices(),
        ];
    }

    public function removeUomRow(int $index): void
    {
        $row = $this->uoms[$index] ?? null;

        if ($row && ! empty($row['id'])) {
            $this->removedUomIds[] = $row['id'];
        }

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

    public function update(): void
    {
        $this->authorize('edit item');

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

        $threshold = $this->threshold;
        $pendingCount = 0;

        DB::transaction(function () use ($validated, $threshold, &$pendingCount) {
            // Update item fields
            $this->item->update([
                ...$validated['inputs'],
                'updated_by' => Auth::id(),
            ]);

            // Process removed UOMs (soft-delete UOM + its prices)
            foreach ($this->removedUomIds as $uomId) {
                $itemUom = ItemUom::find($uomId);
                if ($itemUom) {
                    // Soft-delete all prices for this UOM
                    foreach ($itemUom->itemPrices as $price) {
                        $price->update(['deleted_by' => Auth::id()]);
                        $price->delete();
                    }
                    $itemUom->update(['deleted_by' => Auth::id()]);
                    $itemUom->delete();
                }
            }

            // Process each UOM row
            foreach ($validated['uoms'] as $uomIndex => $uomData) {
                $existingUomId = $this->uoms[$uomIndex]['id'] ?? null;

                if ($existingUomId) {
                    // Update existing ItemUom
                    $itemUom = ItemUom::find($existingUomId);
                    if (! $itemUom) {
                        continue;
                    }
                    $itemUom->update([
                        'uom_id' => $uomData['uom_id'],
                        'conversion_rate' => $uomData['conversion_rate'],
                        'is_base' => $uomData['is_base'],
                        'remarks' => $uomData['remarks'] ?? null,
                        'updated_by' => Auth::id(),
                    ]);
                } else {
                    // Create new ItemUom
                    $itemUom = ItemUom::create([
                        'item_id' => $this->item->id,
                        'uom_id' => $uomData['uom_id'],
                        'conversion_rate' => $uomData['conversion_rate'],
                        'is_base' => $uomData['is_base'],
                        'remarks' => $uomData['remarks'] ?? null,
                        'created_by' => Auth::id(),
                    ]);
                }

                // Process prices for this UOM
                foreach ($uomData['prices'] ?? [] as $priceIndex => $priceData) {
                    $existingPriceId = $this->uoms[$uomIndex]['prices'][$priceIndex]['id'] ?? null;
                    $originalPrice = (float) ($this->uoms[$uomIndex]['prices'][$priceIndex]['original_price'] ?? 0);

                    if ($existingPriceId) {
                        // Update existing price
                        $itemPrice = ItemPrice::find($existingPriceId);
                        if (! $itemPrice) {
                            continue;
                        }

                        $oldPrice = $originalPrice;
                        $newPrice = (float) $priceData['price'];

                        // Check if price changed and if approval is needed
                        if ($oldPrice !== $newPrice && $threshold > 0) {
                            $changePercentage = $oldPrice <= 0 ? 100.00 : (($newPrice - $oldPrice) / $oldPrice) * 100;

                            if (abs($changePercentage) > $threshold) {
                                // Auto-reject any existing pending for this item price
                                PendingItemPrice::where('item_price_id', $existingPriceId)
                                    ->where('status', 'pending')
                                    ->update([
                                        'status' => 'rejected',
                                        'approved_by' => Auth::id(),
                                        'reviewed_at' => now(),
                                        'processed_at' => now(),
                                        'approval_notes' => 'Auto-rejected: New price change submitted',
                                        'updated_by' => Auth::id(),
                                    ]);

                                // Submit for approval
                                PendingItemPrice::create([
                                    'item_price_id' => $existingPriceId,
                                    'item_uom_id' => $itemUom->id,
                                    'category_price_id' => $priceData['category_price_id'],
                                    'old_price' => $oldPrice,
                                    'new_price' => $newPrice,
                                    'change_percentage' => $changePercentage,
                                    'status' => 'pending',
                                    'submitted_by' => Auth::id(),
                                    'submitted_at' => now(),
                                    'created_by' => Auth::id(),
                                ]);

                                // Update non-price fields only
                                $itemPrice->update([
                                    'remarks' => $priceData['remarks'],
                                    'is_active' => $priceData['is_active'],
                                    'updated_by' => Auth::id(),
                                ]);

                                $pendingCount++;

                                continue;
                            }
                        }

                        // Direct update (below threshold or threshold disabled)
                        if ($oldPrice !== $newPrice) {
                            HistoryItemPrice::create([
                                'item_uom_id' => $itemUom->id,
                                'category_price_id' => $priceData['category_price_id'],
                                'old_price' => $oldPrice,
                                'new_price' => $newPrice,
                                'created_by' => Auth::id(),
                            ]);
                        }

                        $itemPrice->update([
                            'price' => $newPrice,
                            'remarks' => $priceData['remarks'],
                            'is_active' => $priceData['is_active'],
                            'updated_by' => Auth::id(),
                        ]);
                    } else {
                        // New price row — check for soft-deleted duplicate
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
                            // Check for active duplicate
                            $existsActive = ItemPrice::where('item_uom_id', $itemUom->id)
                                ->where('category_price_id', $priceData['category_price_id'])
                                ->exists();

                            if ($existsActive) {
                                continue;
                            }

                            ItemPrice::create([
                                'item_uom_id' => $itemUom->id,
                                'category_price_id' => $priceData['category_price_id'],
                                'price' => $priceData['price'],
                                'remarks' => $priceData['remarks'],
                                'is_active' => $priceData['is_active'],
                                'created_by' => Auth::id(),
                            ]);
                        }

                        // Log price history for new row
                        HistoryItemPrice::create([
                            'item_uom_id' => $itemUom->id,
                            'category_price_id' => $priceData['category_price_id'],
                            'old_price' => 0,
                            'new_price' => $priceData['price'],
                            'created_by' => Auth::id(),
                        ]);
                    }
                }
            }
        });

        if ($pendingCount > 0) {
            Flux::toast("Item updated. {$pendingCount} price change(s) submitted for approval.", variant: 'info', position: 'top right');
        } else {
            Flux::toast('Item updated successfully', variant: 'success', position: 'top right');
        }

        $this->redirectRoute('inventories.items.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.inventories.item.edit');
    }
}
