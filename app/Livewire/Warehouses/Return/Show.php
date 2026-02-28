<?php

namespace App\Livewire\Warehouses\Return;

use App\Models\CMW\Inventory\InventoryDamagedStock;
use App\Models\CMW\Inventory\InventoryLedger;
use App\Models\CMW\Transaction\ReturnHeader;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Receive Return Goods')]
class Show extends Component
{
    public $returnHeader = null;

    public $lines = [];

    public $dropdown_warehouses = [];

    public function mount($id): void
    {
        $this->authorize('view warehouse return');

        $this->returnHeader = ReturnHeader::with([
            'partner', 'orderHeader.company', 'orderHeader.currency',
            'deliveryHeader.details', 'details.item', 'details.itemUom.uom',
            'details.deliveryDetail',
        ])->findOrFail($id);

        // Guard: only PROCESSING status with no warehouse receipt
        if (! $this->returnHeader->isProcessing() || $this->returnHeader->isWarehouseReceived()) {
            Flux::toast('This return is not pending warehouse receipt.', variant: 'danger', position: 'top-end');
            $this->redirectRoute('warehouses.return.index', navigate: true);

            return;
        }

        $this->loadWarehouses();
        $this->loadLines();
    }

    protected function loadWarehouses(): void
    {
        $this->dropdown_warehouses = \App\Models\CMW\Master\Warehouse::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($w) => ['value' => $w->id, 'label' => $w->name])
            ->toArray();
    }

    protected function loadLines(): void
    {
        $this->lines = $this->returnHeader->details->map(function ($detail) {
            // Default warehouse from the original DO detail
            $defaultWarehouseId = $detail->deliveryDetail?->warehouse_id ?? null;

            return [
                'id' => $detail->id,
                'item_code' => $detail->item?->code ?? '',
                'item_name' => $detail->item?->name ?? '',
                'uom_name' => $detail->itemUom?->uom?->name ?? '',
                'item_id' => $detail->item_id,
                'item_uom_id' => $detail->item_uom_id,
                'quantity_return' => (float) $detail->quantity_return,
                'quantity_received_good' => (float) $detail->quantity_return, // default: all good
                'quantity_received_damaged' => 0,
                'warehouse_id' => $defaultWarehouseId,
            ];
        })->toArray();
    }

    public function updatedLines($value, $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) === 2) {
            $index = (int) $parts[0];
            $field = $parts[1];

            if (in_array($field, ['quantity_received_good', 'quantity_received_damaged'])) {
                $this->syncQuantities($index, $field);
            }
        }
    }

    protected function syncQuantities(int $index, string $changedField): void
    {
        if (! isset($this->lines[$index])) {
            return;
        }

        $line = &$this->lines[$index];
        $maxQty = (float) $line['quantity_return'];

        if ($changedField === 'quantity_received_good') {
            $good = max(0, min((float) $line['quantity_received_good'], $maxQty));
            $line['quantity_received_good'] = $good;
            $line['quantity_received_damaged'] = $maxQty - $good;
        } else {
            $damaged = max(0, min((float) $line['quantity_received_damaged'], $maxQty));
            $line['quantity_received_damaged'] = $damaged;
            $line['quantity_received_good'] = $maxQty - $damaged;
        }
    }

    public function confirmReceipt(): void
    {
        $this->authorize('receive warehouse return');

        // Validate all lines
        foreach ($this->lines as $index => $line) {
            $total = (float) $line['quantity_received_good'] + (float) $line['quantity_received_damaged'];
            $expected = (float) $line['quantity_return'];

            if (abs($total - $expected) > 0.01) {
                Flux::toast("Line #{$index}: Good ({$line['quantity_received_good']}) + Damaged ({$line['quantity_received_damaged']}) must equal Return Qty ({$expected}).", variant: 'danger', position: 'top-end');

                return;
            }

            if (! $line['warehouse_id']) {
                Flux::toast("Line #{$index}: Warehouse is required.", variant: 'danger', position: 'top-end');

                return;
            }
        }

        try {
            DB::transaction(function () {
                foreach ($this->lines as $line) {
                    $detail = $this->returnHeader->details()->find($line['id']);
                    if (! $detail) {
                        continue;
                    }

                    $detail->update([
                        'quantity_received_good' => (float) $line['quantity_received_good'],
                        'quantity_received_damaged' => (float) $line['quantity_received_damaged'],
                        'updated_by' => Auth::id(),
                    ]);

                    // Create inventory ledger entry for good items (stock-in)
                    $goodQty = (float) $line['quantity_received_good'];
                    if ($goodQty > 0) {
                        // Convert to base UOM if needed
                        $baseGoodQty = $goodQty;
                        $itemUomId = $line['item_uom_id'] ? (int) $line['item_uom_id'] : null;
                        if ($itemUomId) {
                            $conversionRate = (float) (\App\Models\CMW\Inventory\ItemUom::where('id', $itemUomId)->value('conversion_rate') ?? 1);
                            $baseGoodQty = $goodQty * $conversionRate;
                        }

                        $currentBalance = (float) (InventoryLedger::where('item_id', $line['item_id'])
                            ->where('warehouse_id', $line['warehouse_id'])
                            ->orderByDesc('date')
                            ->orderByDesc('id')
                            ->value('balance') ?? 0);

                        InventoryLedger::create([
                            'item_id' => $line['item_id'],
                            'warehouse_id' => $line['warehouse_id'],
                            'item_uom_id' => $line['item_uom_id'],
                            'quantity_in' => $baseGoodQty,
                            'quantity_out' => 0,
                            'balance' => $currentBalance + $baseGoodQty,
                            'date' => now()->toDateString(),
                            'type' => 'sales_return',
                            'reference_type' => ReturnHeader::class,
                            'reference_id' => $this->returnHeader->id,
                            'remarks' => "Return receipt (good): {$this->returnHeader->code}",
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                        ]);
                    }

                    // Create inventory damaged stock entry for damaged items
                    $damagedQty = (float) $line['quantity_received_damaged'];
                    if ($damagedQty > 0) {
                        InventoryDamagedStock::create([
                            'item_id' => $line['item_id'],
                            'warehouse_id' => $line['warehouse_id'],
                            'item_uom_id' => $line['item_uom_id'],
                            'quantity' => $damagedQty,
                            'date' => now()->toDateString(),
                            'reference_type' => \App\Models\CMW\Transaction\ReturnDetail::class,
                            'reference_id' => $line['id'],
                            'remarks' => "Damaged return: {$this->returnHeader->code}",
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                        ]);
                    }
                }

                // Update header: set received info
                $this->returnHeader->update([
                    'received_by' => Auth::id(),
                    'received_at' => now(),
                    'updated_by' => Auth::id(),
                ]);

                // For INVOICE type: reduce AR balance and set to FINISH
                if ($this->returnHeader->return_type === 'INVOICE') {
                    $this->processInvoiceReturn();
                }
                // For ITEM type: stays PROCESSING (sales will allocate)

                $this->dispatch('shp.warehouse.return.received', returnId: $this->returnHeader->id);
            });

            Flux::toast("Return {$this->returnHeader->code} goods received.", variant: 'success', position: 'top-end');
            $this->redirectRoute('warehouses.return.index', navigate: true);
        } catch (\Exception $e) {
            Flux::toast('Error: '.$e->getMessage(), variant: 'danger', position: 'top-end');
        }
    }

    protected function processInvoiceReturn(): void
    {
        $arInvoice = $this->returnHeader->arInvoice;
        if (! $arInvoice) {
            return;
        }

        $returnTotal = (float) $this->returnHeader->total;

        // Reduce invoice balance
        $newBalance = max(0, (float) $arInvoice->balance - $returnTotal);
        $updateData = [
            'balance' => round($newBalance, 2),
            'updated_by' => Auth::id(),
        ];

        if ($newBalance <= 0) {
            $updateData['status'] = 'paid';
        }

        $arInvoice->update($updateData);

        // Set return to FINISH
        $this->returnHeader->update([
            'status' => ReturnHeader::STATUS_FINISH,
            'updated_by' => Auth::id(),
        ]);
    }

    public function render()
    {
        return view('livewire.warehouses.return.show');
    }
}
