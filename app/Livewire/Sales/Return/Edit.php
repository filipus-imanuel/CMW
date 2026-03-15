<?php

namespace App\Livewire\Sales\Return;

use App\Helpers\CMW\TransactionHelper;
use App\Models\CMW\Transaction\ReturnDetail;
use App\Models\CMW\Transaction\ReturnHeader;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Sales Return')]
class Edit extends Component
{
    public $returnHeader = null;

    public $items = [];

    public $inputs = [];

    public $pendingRemoveIndex = null;

    public function rules(): array
    {
        return [
            'inputs.return_type' => 'required|in:ITEM,ITEM_INVOICE,INVOICE_RETURN,INVOICE_DISCARD',
        ];
    }

    public function mount($id): void
    {
        $this->authorize('edit sales return');

        $this->returnHeader = ReturnHeader::with([
            'partner', 'orderHeader.company', 'orderHeader.currency',
            'deliveryHeader', 'details.item', 'details.itemUom.uom',
            'details.deliveryDetail',
        ])->findOrFail($id);

        // Guard: only INIT status
        if (! $this->returnHeader->isInit()) {
            Flux::toast('This return can no longer be edited.', variant: 'danger', position: 'top-end');
            $this->redirectRoute('sales.return.show', ['id' => $id], navigate: true);

            return;
        }

        $this->inputs = [
            'return_type' => $this->returnHeader->return_type,
            'remarks' => $this->returnHeader->remarks ?? '',
        ];

        $this->loadItems();
    }

    protected function loadItems(): void
    {
        $this->items = $this->returnHeader->details->map(function ($detail) {
            $maxQty = (float) ($detail->deliveryDetail?->quantity_received ?? 0);

            return [
                'id' => $detail->id,
                'delivery_detail_id' => $detail->delivery_detail_id,
                'item_id' => $detail->item_id,
                'item_uom_id' => $detail->item_uom_id,
                'item_code' => $detail->item?->code ?? '',
                'item_name' => $detail->item?->name ?? '',
                'uom_name' => $detail->itemUom?->uom?->name ?? '',
                'max_quantity' => $maxQty,
                'quantity_return' => (float) $detail->quantity_return,
                'price' => (float) $detail->price,
                'discount' => (float) $detail->discount,
                'tax' => (float) $detail->tax,
                'total' => (float) $detail->total,
            ];
        })->toArray();
    }

    public function updatedItems($value, $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'quantity_return') {
            $index = (int) $parts[0];
            $this->recalculateLine($index);
        }
    }

    protected function recalculateLine(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $line = &$this->items[$index];
        $qty = max(0, min((float) $line['quantity_return'], (float) $line['max_quantity']));
        $line['quantity_return'] = $qty;

        $order = $this->returnHeader->orderHeader;
        $taxMode = $order->tax_mode ?? 'NONE';
        $taxRate = (float) ($order->tax_rate ?? 0);

        $calc = TransactionHelper::calculateItemTax($qty, (float) $line['price'], (float) $line['discount'], $taxMode, $taxRate);
        $line['tax'] = $calc['tax'];
        $line['total'] = $calc['total'];
    }

    public function removePendingLine(): void
    {
        if ($this->pendingRemoveIndex !== null) {
            $this->removeLine((int) $this->pendingRemoveIndex);
            $this->pendingRemoveIndex = null;
        }
    }

    public function removeLine(int $index): void
    {
        if (count($this->items) <= 1) {
            Flux::toast('At least one item is required.', variant: 'danger', position: 'top-end');

            return;
        }

        $item = $this->items[$index] ?? null;
        if ($item && isset($item['id'])) {
            ReturnDetail::destroy($item['id']);
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save(): void
    {
        $this->authorize('edit sales return');
        $this->validate();

        $activeItems = collect($this->items)->filter(fn ($item) => (float) $item['quantity_return'] > 0);
        if ($activeItems->isEmpty()) {
            Flux::toast('At least one item with quantity > 0 is required.', variant: 'danger', position: 'top-end');

            return;
        }

        try {
            DB::transaction(function () use ($activeItems) {
                $totalSubtotal = 0;
                $totalTax = 0;
                $grandTotal = 0;

                foreach ($activeItems as $item) {
                    if (isset($item['id'])) {
                        ReturnDetail::where('id', $item['id'])->update([
                            'quantity_return' => $item['quantity_return'],
                            'tax' => $item['tax'],
                            'total' => $item['total'],
                            'updated_by' => Auth::id(),
                        ]);
                    }

                    $totalSubtotal += (float) $item['total'] - (float) $item['tax'];
                    $totalTax += (float) $item['tax'];
                    $grandTotal += (float) $item['total'];
                }

                $this->returnHeader->update([
                    'return_type' => $this->inputs['return_type'],
                    'subtotal' => round($totalSubtotal, 2),
                    'tax' => round($totalTax, 2),
                    'total' => round($grandTotal, 2),
                    'remarks' => $this->inputs['remarks'] ?? null,
                    'updated_by' => Auth::id(),
                ]);

                Flux::toast('Sales return saved.', variant: 'success', position: 'top-end');
            });
        } catch (\Exception $e) {
            Flux::toast('Error saving: '.$e->getMessage(), variant: 'danger', position: 'top-end');
        }
    }

    public function submit(): void
    {
        $this->authorize('edit sales return');
        $this->validate();

        $activeItems = collect($this->items)->filter(fn ($item) => (float) $item['quantity_return'] > 0);
        if ($activeItems->isEmpty()) {
            Flux::toast('At least one item with quantity > 0 is required.', variant: 'danger', position: 'top-end');

            return;
        }

        try {
            DB::transaction(function () use ($activeItems) {
                // Save items first
                $totalSubtotal = 0;
                $totalTax = 0;
                $grandTotal = 0;

                foreach ($activeItems as $item) {
                    if (isset($item['id'])) {
                        ReturnDetail::where('id', $item['id'])->update([
                            'quantity_return' => $item['quantity_return'],
                            'tax' => $item['tax'],
                            'total' => $item['total'],
                            'updated_by' => Auth::id(),
                        ]);
                    }

                    $totalSubtotal += (float) $item['total'] - (float) $item['tax'];
                    $totalTax += (float) $item['tax'];
                    $grandTotal += (float) $item['total'];
                }

                // Update header and submit
                $this->returnHeader->update([
                    'return_type' => $this->inputs['return_type'],
                    'subtotal' => round($totalSubtotal, 2),
                    'tax' => round($totalTax, 2),
                    'total' => round($grandTotal, 2),
                    'remarks' => $this->inputs['remarks'] ?? null,
                    'status' => ReturnHeader::STATUS_APPROVAL,
                    'updated_by' => Auth::id(),
                ]);

                $this->dispatch('shp.sales.return.submitted', returnId: $this->returnHeader->id);

                Flux::toast("Return {$this->returnHeader->code} submitted for approval.", variant: 'success', position: 'top-end');
                $this->redirectRoute('sales.return.index.draft', navigate: true);
            });
        } catch (\Exception $e) {
            Flux::toast('Error submitting: '.$e->getMessage(), variant: 'danger', position: 'top-end');
        }
    }

    public function render()
    {
        return view('livewire.sales.return.edit');
    }
}
