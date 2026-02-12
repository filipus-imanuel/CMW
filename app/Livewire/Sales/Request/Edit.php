<?php

namespace App\Livewire\Sales\Request;

use App\Helpers\CMW\CustomerCheckHelper;
use App\Helpers\CMW\PopulateDataHelper;
use App\Helpers\CMW\TransactionHelper;
use App\Models\CMW\Master\Tax;
use App\Models\CMW\Transaction\OrderDetail;
use App\Models\CMW\Transaction\OrderHeader;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function mount($id): void
    {
        $this->authorize('edit sales request');

        $this->order = OrderHeader::with(['partner', 'company', 'itemCategory', 'currency', 'details.item', 'details.uom'])
            ->findOrFail($id);

        // Status guard - only INIT can be edited
        if ($this->order->status !== 'INIT') {
            $this->redirectRoute('sales.request.index.init', navigate: true);

            return;
        }

        $this->loadDropdownData();
        $this->handlePopulateInputs();
        $this->runChecks();
    }

    public function loadDropdownData(): void
    {
        $this->dropdown_data['taxes'] = PopulateDataHelper::getTaxes();
    }

    public function handlePopulateInputs(): void
    {
        $this->inputs = [
            'date' => $this->order->date?->format('Y-m-d'),
            'remarks' => $this->order->remarks ?? '',
            'tax_mode' => $this->order->tax_mode ?? 'NONE',
            'tax_id' => $this->order->tax_id ?? '',
        ];

        $taxMode = $this->inputs['tax_mode'];
        $taxRate = (float) ($this->order->tax_rate ?? 0);

        $this->items = $this->order->details->map(function ($detail) use ($taxMode, $taxRate) {
            $calc = TransactionHelper::calculateItemTax(
                (float) $detail->quantity,
                (float) $detail->price,
                (float) $detail->discount,
                $taxMode,
                $taxRate
            );

            return [
                'id' => $detail->id,
                'item_id' => $detail->item_id,
                'item_code' => $detail->item?->code ?? '',
                'item_name' => $detail->item?->name ?? '',
                'uom_id' => $detail->uom_id,
                'uom_name' => $detail->uom?->name ?? '',
                'quantity' => number_format((float) $detail->quantity, 2, '.', ''),
                'price' => number_format((float) $detail->price, 2, '.', ''),
                'discount' => number_format((float) $detail->discount, 2, '.', ''),
                'tax' => number_format($calc['tax'], 2, '.', ''),
                'total' => number_format($calc['total'], 2, '.', ''),
            ];
        })->toArray();
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
            (float) $this->order->total
        );
    }

    /**
     * Handle item selected from SearchItem modal.
     */
    #[On('sales.request.item-selected')]
    public function addItem(int $itemId, string $itemCode, string $itemName, int $uomId, string $uomName, float $sellPrice): void
    {
        // Check if item already exists in the list
        foreach ($this->items as $item) {
            if ($item['item_id'] === $itemId) {
                Flux::toast('Item already added to the list', variant: 'warning', position: 'top-end');

                return;
            }
        }

        $this->items[] = [
            'id' => null,
            'item_id' => $itemId,
            'item_code' => $itemCode,
            'item_name' => $itemName,
            'uom_id' => $uomId,
            'uom_name' => $uomName,
            'quantity' => '1.00',
            'price' => number_format($sellPrice, 2, '.', ''),
            'discount' => '0.00',
            'tax' => '0.00',
            'total' => number_format($sellPrice, 2, '.', ''),
        ];

        $this->recalculateItemTotal(count($this->items) - 1);
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
        $price = (float) ($this->items[$index]['price'] ?? 0);
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
        // $key format: "0.quantity", "1.price", etc.
        $parts = explode('.', $key);
        if (count($parts) === 2) {
            $index = (int) $parts[0];
            $this->recalculateItemTotal($index);
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
            'inputs.remarks' => 'nullable|string|max:1024',
            'inputs.tax_mode' => 'required|in:INCLUDE,EXCLUDE,NONE',
            'inputs.tax_id' => 'nullable|required_if:inputs.tax_mode,INCLUDE,EXCLUDE|exists:taxes,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.uom_id' => 'required|exists:uoms,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

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
                $price = (float) $item['price'];
                $discount = (float) ($item['discount'] ?? 0);

                $calc = TransactionHelper::calculateItemTax($qty, $price, $discount, $taxMode, $taxRate);

                $detailData = [
                    'order_header_id' => $this->order->id,
                    'item_id' => $item['item_id'],
                    'uom_id' => $item['uom_id'],
                    'quantity' => $qty,
                    'price' => $price,
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
        $this->order = $this->order->fresh(['partner', 'company', 'itemCategory', 'currency', 'details.item', 'details.uom']);
        $this->handlePopulateInputs();
        $this->runChecks();

        Flux::toast('Sales request saved successfully', variant: 'success', position: 'top-end');
        $this->dispatch('sales.request.refresh.init');
    }

    /**
     * Submit the request - changes status based on checks.
     */
    public function submit(): void
    {
        $this->authorize('edit sales request');

        // Must have items
        if (empty($this->items)) {
            Flux::toast('Please add at least one item before submitting', variant: 'danger', position: 'top-end');

            return;
        }

        // Save first
        $this->save();

        DB::transaction(function () {
            // Run final checks
            $checks = CustomerCheckHelper::runAllChecks(
                $this->order->partner_id,
                $this->order->company_id,
                $this->order->item_category_id,
                (float) $this->order->total
            );

            if ($checks['has_issues']) {
                // Issues found - send to approval
                $this->order->update([
                    'status' => 'APPROVAL',
                    'updated_by' => Auth::id(),
                ]);

                Flux::toast('Sales request submitted for approval (issues detected)', variant: 'warning', position: 'top-end');
            } else {
                // No issues - directly approved
                $this->order->update([
                    'status' => 'REQUEST',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'updated_by' => Auth::id(),
                ]);

                Flux::toast('Sales request approved and submitted', variant: 'success', position: 'top-end');
            }
        });

        $this->dispatch('sales.request.refresh.init');
        $this->dispatch('sales.request.refresh.approval');
        $this->dispatch('sales.request.refresh.request');
        $this->redirectRoute('sales.request.index.init', navigate: true);
    }

    public function render()
    {
        return view('livewire.sales.request.edit');
    }
}
