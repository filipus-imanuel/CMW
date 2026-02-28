<?php

namespace App\Livewire\Sales\Request;

use App\Helpers\CMW\CustomerCheckHelper;
use App\Helpers\CMW\PopulateDataHelper;
use App\Helpers\CMW\PriceResolutionHelper;
use App\Helpers\CMW\TransactionHelper;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Master\Tax;
use App\Models\CMW\System\Setting;
use App\Models\CMW\Transaction\OrderDetail;
use App\Models\CMW\Transaction\OrderHeader;
use App\Models\CMW\Transaction\ReturnDetail;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Sales Request')]
class Edit extends Component
{
    public ?OrderHeader $order = null;

    public $inputs = [];

    public $items = [];

    public $checks = [];

    public $dropdown_data = [];

    public $deleteItemId = null;

    /**
     * Available return items for this partner (quantity_next_so > 0, not consumed).
     */
    public $returnItems = [];

    /**
     * IDs of selected return detail items to attach.
     */
    public $selectedReturnItems = [];

    /**
     * Per-item guardrail data: het_price, floor_price, resolved_price, warnings.
     *
     * @var array<int, array{het_price: float, floor_price: float, resolved_price: float, warnings: array<string>}>
     */
    public $priceGuardrails = [];

    /**
     * Get the floor percentage from system settings.
     */
    #[Computed]
    public function floorPercentage(): float
    {
        return (float) Setting::get('sales.request.floor_percentage_of_het', 80);
    }

    public function mount($id): void
    {
        $this->authorize('edit sales request');

        $this->order = OrderHeader::with(['partner', 'company', 'itemCategory', 'currency', 'details.item', 'details.itemUom.uom'])
            ->findOrFail($id);

        // Status guard - only INIT can be edited
        if ($this->order->status !== 'INIT') {
            $this->redirectRoute('sales.request.index.init', navigate: true);

            return;
        }

        $this->loadDropdownData();
        $this->handlePopulateInputs();
        $this->runChecks();
        $this->loadReturnItems();
    }

    public function loadDropdownData(): void
    {
        $this->dropdown_data['taxes'] = PopulateDataHelper::getTaxes();
    }

    public function handlePopulateInputs(): void
    {
        $this->inputs = [
            'date' => $this->order->date?->format('Y-m-d'),
            'delivery_date' => $this->order->delivery_date?->format('Y-m-d') ?? '',
            'remarks' => $this->order->remarks ?? '',
            'tax_mode' => $this->order->tax_mode ?? 'NONE',
            'tax_id' => $this->order->tax_id ?? '',
        ];

        $taxMode = $this->inputs['tax_mode'];
        $taxRate = (float) ($this->order->tax_rate ?? 0);

        // Load items with their Item model for HET
        $partner = $this->order->partner;
        $detailItems = $this->order->details->pluck('item')->filter()->unique('id');
        $floorPct = $this->floorPercentage;

        $this->items = $this->order->details->map(function ($detail) use ($taxMode, $taxRate) {
            $calc = TransactionHelper::calculateItemTax(
                (float) $detail->quantity,
                (float) $detail->price_proposed,
                (float) $detail->discount,
                $taxMode,
                $taxRate
            );

            $isReturn = ! empty($detail->return_detail_id);

            return [
                'id' => $detail->id,
                'item_id' => $detail->item_id,
                'item_code' => $detail->item?->code ?? '',
                'item_name' => ($isReturn ? '↩ ' : '').($detail->item?->name ?? ''),
                'item_uom_id' => $detail->item_uom_id,
                'uom_name' => $detail->itemUom?->uom?->name ?? '',
                'quantity' => number_format((float) $detail->quantity, 2, '.', ''),
                'price_proposed' => number_format((float) $detail->price_proposed, 2, '.', ''),
                'price_deal' => number_format((float) ($detail->price_deal ?: $detail->price_proposed), 2, '.', ''),
                'discount' => number_format((float) $detail->discount, 2, '.', ''),
                'tax' => number_format($calc['tax'], 2, '.', ''),
                'total' => number_format($calc['total'], 2, '.', ''),
                'return_detail_id' => $detail->return_detail_id,
            ];
        })->toArray();

        // Populate selectedReturnItems from existing return-origin details
        $this->selectedReturnItems = $this->order->details
            ->whereNotNull('return_detail_id')
            ->pluck('return_detail_id')
            ->values()
            ->toArray();

        // Build guardrails for each item row
        $this->priceGuardrails = [];
        foreach ($this->order->details as $index => $detail) {
            $item = $this->items[$index] ?? null;
            if ($item === null) {
                continue;
            }
            $detailItem = $detailItems->firstWhere('id', $item['item_id']);
            $hetResolved = $detailItem
                ? PriceResolutionHelper::resolve($detailItem, null, $detail->item_uom_id)
                : ['price' => 0.0];
            $hetPrice = $hetResolved['price'];
            $partnerResolved = $detailItem
                ? PriceResolutionHelper::resolve($detailItem, $partner, $detail->item_uom_id)
                : ['price' => 0.0];
            $resolvedPrice = $partnerResolved['price'];
            $floorPrice = $hetPrice * ($floorPct / 100);

            $this->priceGuardrails[$index] = [
                'het_price' => $hetPrice,
                'floor_price' => round($floorPrice, 2),
                'resolved_price' => $resolvedPrice,
                'warnings' => $this->computePriceWarnings((float) $item['price_proposed'], $hetPrice, $floorPrice),
            ];
        }
    }

    public function runChecks(): void
    {
        if (! $this->order) {
            return;
        }

        $this->checks = CustomerCheckHelper::runAllChecks(
            $this->order->partner_id,
            $this->order->company_id,
            $this->order->item_category_id,
            (float) $this->order->total,
            $this->order->id
        );
    }

    /**
     * Handle item selected from SearchItem modal.
     */
    #[On('sales.request.item-selected')]
    public function addItem(int $itemId, string $itemCode, string $itemName, int $itemUomId, string $uomName, float $sellPrice, float $hetPrice): void
    {
        // Check if same item+UOM combination already exists in the list
        foreach ($this->items as $item) {
            if ($item['item_uom_id'] === $itemUomId) {
                Flux::toast('Item already added to the list', variant: 'warning', position: 'top-end');

                return;
            }
        }

        $this->items[] = [
            'id' => null,
            'item_id' => $itemId,
            'item_code' => $itemCode,
            'item_name' => $itemName,
            'item_uom_id' => $itemUomId,
            'uom_name' => $uomName,
            'quantity' => '1.00',
            'price_proposed' => number_format($sellPrice, 2, '.', ''),
            'price_deal' => number_format($sellPrice, 2, '.', ''),
            'discount' => '0.00',
            'tax' => '0.00',
            'total' => number_format($sellPrice, 2, '.', ''),
        ];

        $newIndex = count($this->items) - 1;

        // Build guardrail for the new item
        $floorPct = $this->floorPercentage;
        $floorPrice = $hetPrice * ($floorPct / 100);
        $this->priceGuardrails[$newIndex] = [
            'het_price' => $hetPrice,
            'floor_price' => round($floorPrice, 2),
            'resolved_price' => $sellPrice,
            'warnings' => $this->computePriceWarnings($sellPrice, $hetPrice, $floorPrice),
        ];

        $this->recalculateItemTotal($newIndex);
    }

    /**
     * Get the current tax rate from the selected tax.
     */
    private function getCurrentTaxRate(): float
    {
        $taxMode = $this->inputs['tax_mode'] ?? 'NONE';
        if ($taxMode === 'NONE' || empty($this->inputs['tax_id'])) {
            return 0;
        }

        return (float) ($this->order->tax_rate ?? 0);
    }

    /**
     * Recalculate a single item row total using TransactionHelper.
     */
    public function recalculateItemTotal(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $qty = (float) ($this->items[$index]['quantity'] ?? 0);
        $price = (float) ($this->items[$index]['price_proposed'] ?? 0);
        $discount = (float) ($this->items[$index]['discount'] ?? 0);
        $taxMode = $this->inputs['tax_mode'] ?? 'NONE';
        $taxRate = $this->getCurrentTaxRate();

        $calc = TransactionHelper::calculateItemTax($qty, $price, $discount, $taxMode, $taxRate);

        $this->items[$index]['tax'] = number_format($calc['tax'], 2, '.', '');
        $this->items[$index]['total'] = number_format($calc['total'], 2, '.', '');
    }

    /**
     * Recalculate all items (e.g. when tax mode/rate changes).
     */
    public function recalculateAllItems(): void
    {
        foreach ($this->items as $index => $item) {
            $this->recalculateItemTotal($index);
        }
    }

    /**
     * Handle tax mode change - reset tax_id if NONE, recalculate all.
     */
    public function updatedInputsTaxMode($value): void
    {
        if ($value === 'NONE') {
            $this->inputs['tax_id'] = '';
            if ($this->order) {
                $this->order->tax_rate = 0;
            }
        }

        $this->recalculateAllItems();
    }

    /**
     * Handle tax selection change - resolve rate and recalculate.
     */
    public function updatedInputsTaxId($value): void
    {
        if ($value && $this->order) {
            $tax = Tax::find($value);
            $this->order->tax_rate = $tax ? (float) $tax->rate : 0;
        } else {
            if ($this->order) {
                $this->order->tax_rate = 0;
            }
        }

        $this->recalculateAllItems();
    }

    /**
     * Called when item fields are updated.
     */
    public function updatedItems($value, $key): void
    {
        // $key format: "0.quantity", "1.price_proposed", etc.
        $parts = explode('.', $key);
        if (count($parts) === 2) {
            $index = (int) $parts[0];
            $field = $parts[1];

            // Auto-calc discount when price_deal, quantity, or price_proposed changes
            if (in_array($field, ['price_deal', 'quantity', 'price_proposed'])) {
                $qty = (float) ($this->items[$index]['quantity'] ?? 0);
                $proposed = (float) ($this->items[$index]['price_proposed'] ?? 0);
                $deal = (float) ($this->items[$index]['price_deal'] ?? 0);
                $discount = max(0, $qty * ($proposed - $deal));
                $this->items[$index]['discount'] = number_format($discount, 2, '.', '');
            }

            $this->recalculateItemTotal($index);

            // Refresh guardrail warnings when price_proposed changes
            if ($field === 'price_proposed' && isset($this->priceGuardrails[$index])) {
                $currentPrice = (float) ($this->items[$index]['price_proposed'] ?? 0);
                $hetPrice = $this->priceGuardrails[$index]['het_price'];
                $floorPrice = $this->priceGuardrails[$index]['floor_price'];
                $this->priceGuardrails[$index]['warnings'] = $this->computePriceWarnings($currentPrice, $hetPrice, $floorPrice);
            }
        }
    }

    /**
     * Confirm item deletion.
     */
    public function confirmDeleteItem(int $index): void
    {
        $this->deleteItemId = $index;
        $this->modal('delete-item-confirmation')->show();
    }

    /**
     * Remove item from the list.
     */
    public function destroyItem(): void
    {
        if ($this->deleteItemId === null) {
            return;
        }

        $index = $this->deleteItemId;

        if (isset($this->items[$index])) {
            // If it has a database ID, delete from DB
            if (! empty($this->items[$index]['id'])) {
                DB::transaction(function () use ($index) {
                    $detail = OrderDetail::find($this->items[$index]['id']);
                    if ($detail) {
                        $detail->update(['deleted_by' => Auth::id()]);
                        $detail->delete();
                    }
                });
            }

            unset($this->items[$index]);
            $this->items = array_values($this->items);

            // Re-index guardrails
            unset($this->priceGuardrails[$index]);
            $this->priceGuardrails = array_values($this->priceGuardrails);

            Flux::toast('Item removed', variant: 'success', position: 'top-end');
        }

        $this->deleteItemId = null;
        $this->modal('delete-item-confirmation')->close();
    }

    /**
     * Save the order with all items.
     */
    public function save(): void
    {
        $this->authorize('edit sales request');

        $this->validate([
            'inputs.date' => 'required|date',
            'inputs.delivery_date' => 'nullable|date',
            'inputs.remarks' => 'nullable|string|max:1024',
            'inputs.tax_mode' => 'required|in:INCLUDE,EXCLUDE,NONE',
            'inputs.tax_id' => 'nullable|required_if:inputs.tax_mode,INCLUDE,EXCLUDE|exists:taxes,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.item_uom_id' => 'required|exists:item_uoms,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price_proposed' => 'required|numeric|min:0',
            'items.*.price_deal' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        // Check if any item price was overridden from its resolved price
        $hasPriceOverride = false;
        foreach ($this->items as $index => $item) {
            $resolvedPrice = $this->priceGuardrails[$index]['resolved_price'] ?? null;
            if ($resolvedPrice !== null && (float) $item['price_proposed'] !== $resolvedPrice) {
                $hasPriceOverride = true;
                break;
            }
        }

        if ($hasPriceOverride) {
            $this->authorize('override price sales request');
        }

        DB::transaction(function () {
            // Resolve tax rate
            $taxMode = $this->inputs['tax_mode'];
            $taxId = $taxMode !== 'NONE' ? ($this->inputs['tax_id'] ?: null) : null;
            $taxRate = 0;
            if ($taxId) {
                $tax = Tax::find($taxId);
                $taxRate = $tax ? (float) $tax->rate : 0;
            }

            // Update header with tax settings
            $this->order->update([
                'date' => $this->inputs['date'],
                'delivery_date' => $this->inputs['delivery_date'] ?: null,
                'remarks' => $this->inputs['remarks'] ?? null,
                'tax_mode' => $taxMode,
                'tax_id' => $taxId,
                'tax_rate' => $taxRate,
                'updated_by' => Auth::id(),
            ]);

            // Sync items
            $existingDetailIds = [];

            foreach ($this->items as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['price_proposed'];
                $discount = (float) ($item['discount'] ?? 0);
                $priceDeal = (float) ($item['price_deal'] ?? 0);

                $calc = TransactionHelper::calculateItemTax($qty, $price, $discount, $taxMode, $taxRate);

                $detailData = [
                    'order_header_id' => $this->order->id,
                    'item_id' => $item['item_id'],
                    'item_uom_id' => $item['item_uom_id'],
                    'return_detail_id' => $item['return_detail_id'] ?? null,
                    'quantity' => $qty,
                    'price_proposed' => $price,
                    'price_deal' => $priceDeal,
                    'discount' => $discount,
                    'tax' => $calc['tax'],
                    'total' => $calc['total'],
                    'updated_by' => Auth::id(),
                ];

                if (! empty($item['id'])) {
                    // Update existing detail
                    $detail = OrderDetail::find($item['id']);
                    if ($detail) {
                        $detail->update($detailData);
                        $existingDetailIds[] = $detail->id;
                    }
                } else {
                    // Create new detail
                    $detailData['created_by'] = Auth::id();
                    $detail = OrderDetail::create($detailData);
                    $existingDetailIds[] = $detail->id;
                }
            }

            // Delete removed details
            $this->order->details()
                ->whereNotIn('id', $existingDetailIds)
                ->each(function ($detail) {
                    $detail->update(['deleted_by' => Auth::id()]);
                    $detail->delete();
                });

            // Recalculate totals
            TransactionHelper::updateOrderTotals($this->order);
        });

        // Refresh data
        $this->order = $this->order->fresh(['partner', 'company', 'itemCategory', 'currency', 'details.item', 'details.itemUom.uom']);
        $this->handlePopulateInputs();
        $this->runChecks();

        Flux::toast('Sales request saved successfully', variant: 'success', position: 'top-end');
        $this->dispatch('sales.request.refresh.init');
    }

    /**
     * Submit the request - sends to APPROVAL status.
     */
    public function submit(): void
    {
        $this->authorize('edit sales request');

        // Must have items
        if (empty($this->items)) {
            Flux::toast('Please add at least one item before submitting', variant: 'danger', position: 'top-end');

            return;
        }

        // Validate delivery_date is required on submit
        // Check non-return items have price_deal > 0
        $nonReturnItems = collect($this->items)->filter(fn ($item) => empty($item['return_detail_id']));
        foreach ($nonReturnItems as $index => $item) {
            if (! isset($item['price_deal']) || (float) $item['price_deal'] <= 0) {
                Flux::toast("Item #{$index}: Price deal must be greater than 0.", variant: 'danger', position: 'top-end');

                return;
            }
        }

        $this->validate([
            'inputs.delivery_date' => 'required|date',
        ], [
            'inputs.delivery_date.required' => 'Delivery date is required before submitting.',
        ]);

        // Save first
        $this->save();

        DB::transaction(function () {
            $this->order->update([
                'status' => 'APPROVAL',
                'updated_by' => Auth::id(),
            ]);

            // Mark linked return items as consumed (from saved order_details)
            $returnDetailIds = $this->order->details()
                ->whereNotNull('return_detail_id')
                ->pluck('return_detail_id')
                ->toArray();

            if (! empty($returnDetailIds)) {
                ReturnDetail::whereIn('id', $returnDetailIds)
                    ->update([
                        'is_next_so_consumed' => true,
                        'consumed_by_order_id' => $this->order->id,
                        'updated_by' => Auth::id(),
                    ]);
            }
        });

        Flux::toast('Sales request submitted for approval', variant: 'success', position: 'top-end');

        $this->dispatch('sales.request.refresh.init');
        $this->dispatch('shp.sales.order.refresh.approval');
        $this->redirectRoute('sales.request.index.init', navigate: true);
    }

    /**
     * Compute guardrail warnings for a given price vs HET and floor.
     *
     * @return array<string>
     */
    private function computePriceWarnings(float $price, float $hetPrice, float $floorPrice): array
    {
        $warnings = [];

        if ($hetPrice > 0 && $price > $hetPrice) {
            $warnings[] = 'above_het';
        }

        if ($floorPrice > 0 && $price < $floorPrice) {
            $warnings[] = 'below_floor';
        }

        return $warnings;
    }

    /**
     * Load available return items matching same partner, company, item category, and tax.
     * Only shows return details where quantity_next_so > 0 and not yet consumed.
     */
    public function loadReturnItems(): void
    {
        if (! $this->order?->partner_id) {
            $this->returnItems = [];

            return;
        }

        $order = $this->order;

        $details = ReturnDetail::with(['header.orderHeader', 'item', 'itemUom.uom'])
            ->where('quantity_next_so', '>', 0)
            ->where('is_next_so_consumed', false)
            ->whereNotIn('id', $this->selectedReturnItems)
            ->whereHas('header', function ($q) use ($order) {
                $q->where('partner_id', $order->partner_id)
                    ->where('company_id', $order->company_id)
                    ->where('status', 'FINISH')
                    ->where('return_type', 'ITEM');
            })
            ->whereHas('header.orderHeader', function ($q) use ($order) {
                $q->where('item_category_id', $order->item_category_id)
                    ->where('tax_mode', $order->tax_mode ?? 'NONE');
                if ($order->tax_mode !== 'NONE' && $order->tax_id) {
                    $q->where('tax_id', $order->tax_id);
                }
            })
            ->get();

        $this->returnItems = $details->map(function ($detail) {
            return [
                'return_detail_id' => $detail->id,
                'return_code' => $detail->header?->code ?? '',
                'item_id' => $detail->item_id,
                'item_uom_id' => $detail->item_uom_id,
                'item_code' => $detail->item?->code ?? '',
                'item_name' => $detail->item?->name ?? '',
                'uom_name' => $detail->itemUom?->uom?->name ?? '',
                'quantity_next_so' => (float) $detail->quantity_next_so,
                'original_so_code' => $detail->header?->orderHeader?->code_order ?? '',
            ];
        })->toArray();
    }

    /**
     * Toggle a return item selection. Adds/removes from items list.
     */
    public function toggleReturnItem(int $detailId): void
    {
        $returnItem = collect($this->returnItems)->firstWhere('return_detail_id', $detailId);
        if (! $returnItem) {
            return;
        }

        if (in_array($detailId, $this->selectedReturnItems)) {
            // Remove
            $this->selectedReturnItems = array_values(array_filter($this->selectedReturnItems, fn ($id) => $id !== $detailId));
            $this->items = array_values(array_filter($this->items, fn ($item) => ($item['return_detail_id'] ?? null) !== $detailId));
            // Re-index guardrails
            $this->priceGuardrails = array_values($this->priceGuardrails);
        } else {
            // Add
            $this->selectedReturnItems[] = $detailId;
            $this->items[] = [
                'id' => null,
                'item_id' => $returnItem['item_id'],
                'item_code' => $returnItem['item_code'],
                'item_name' => '↩ '.$returnItem['item_name'],
                'item_uom_id' => $returnItem['item_uom_id'],
                'uom_name' => $returnItem['uom_name'],
                'quantity' => number_format($returnItem['quantity_next_so'], 2, '.', ''),
                'price_proposed' => '0.00',
                'price_deal' => '0.00',
                'discount' => '0.00',
                'tax' => '0.00',
                'total' => '0.00',
                'return_detail_id' => $detailId,
            ];
            $this->priceGuardrails[] = [
                'het_price' => 0,
                'floor_price' => 0,
                'resolved_price' => 0,
                'warnings' => [],
            ];
        }
    }

    public function render()
    {
        return view('livewire.sales.request.edit');
    }
}
