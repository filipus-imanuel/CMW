<?php

namespace App\Livewire\Sales\Return;

use App\Helpers\CMW\CodeGeneratorHelper;
use App\Helpers\CMW\PopulateDataHelper;
use App\Helpers\CMW\TransactionHelper;
use App\Models\CMW\Transaction\DeliveryHeader;
use App\Models\CMW\Transaction\OrderHeader;
use App\Models\CMW\Transaction\ReturnDetail;
use App\Models\CMW\Transaction\ReturnHeader;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Sales Return')]
class Create extends Component
{
    public $inputs = [];

    public $items = [];

    public $dropdown_data = [];

    public $selectedDelivery = null;

    public $selectedOrder = null;

    public function rules(): array
    {
        return [
            'inputs.return_type' => 'required|in:ITEM,INVOICE',
            'inputs.date' => 'required|date',
            'inputs.remarks' => 'nullable|string|max:1024',
        ];
    }

    public function mount($deliveryId = null): void
    {
        $this->authorize('create sales return');

        $this->inputs = [
            'partner_id' => '',
            'order_id' => '',
            'delivery_id' => '',
            'return_type' => 'ITEM',
            'date' => now()->format('Y-m-d'),
            'remarks' => '',
        ];

        $this->loadDropdownData();

        if ($deliveryId) {
            $this->loadFromDelivery($deliveryId);
        }
    }

    public function loadDropdownData(): void
    {
        $user = Auth::user();

        if ($user->hasRole('Super Admin')) {
            $this->dropdown_data['customers'] = PopulateDataHelper::getCustomers(['useCache' => false]);
        } else {
            $this->dropdown_data['customers'] = PopulateDataHelper::get(\App\Models\CMW\Master\Partner::class, [
                'filters' => ['is_customer' => true, 'user_id' => $user->id],
                'useCache' => false,
            ]);
        }

        $this->dropdown_data['orders'] = [];
        $this->dropdown_data['deliveries'] = [];
    }

    public function loadFromDelivery($deliveryId): void
    {
        $delivery = DeliveryHeader::with([
            'orderHeader.partner', 'orderHeader.company', 'orderHeader.currency',
            'details.item', 'details.itemUom.uom',
        ])->findOrFail($deliveryId);

        // Guard: only finished DO
        if ($delivery->status !== 'finished') {
            Flux::toast('Only finished delivery orders can be returned.', variant: 'danger', position: 'top-end');
            $this->redirectRoute('sales.return.index.draft', navigate: true);

            return;
        }

        // Guard: no existing active return for this DO
        $existingReturn = ReturnHeader::where('delivery_header_id', $delivery->id)
            ->where('status', '!=', ReturnHeader::STATUS_CANCELLED)
            ->exists();

        if ($existingReturn) {
            Flux::toast('A return already exists for this delivery order.', variant: 'danger', position: 'top-end');
            $this->redirectRoute('sales.return.index.draft', navigate: true);

            return;
        }

        $this->selectedDelivery = $delivery;
        $this->selectedOrder = $delivery->orderHeader;

        $this->inputs['partner_id'] = $this->selectedOrder->partner_id;
        $this->inputs['order_id'] = $this->selectedOrder->id;
        $this->inputs['delivery_id'] = $delivery->id;

        $this->populateItemsFromDelivery();
    }

    /**
     * When customer changes, load available SO.
     */
    public function updatedInputsPartnerId($value): void
    {
        $this->dropdown_data['orders'] = [];
        $this->dropdown_data['deliveries'] = [];
        $this->inputs['order_id'] = '';
        $this->inputs['delivery_id'] = '';
        $this->items = [];
        $this->selectedDelivery = null;
        $this->selectedOrder = null;

        if (! $value) {
            return;
        }

        $this->dropdown_data['orders'] = OrderHeader::where('partner_id', $value)
            ->whereIn('status', ['DELIVERY', 'FINISH'])
            ->orderByDesc('date')
            ->get()
            ->map(fn ($o) => ['value' => $o->id, 'label' => "{$o->code_order} ({$o->date->format('d M Y')})"])
            ->toArray();
    }

    /**
     * When SO changes, load available DOs.
     */
    public function updatedInputsOrderId($value): void
    {
        $this->dropdown_data['deliveries'] = [];
        $this->inputs['delivery_id'] = '';
        $this->items = [];
        $this->selectedDelivery = null;
        $this->selectedOrder = null;

        if (! $value) {
            return;
        }

        $this->selectedOrder = OrderHeader::with(['partner', 'company', 'currency'])->find($value);

        // Get finished DOs without active returns
        $activeReturnDeliveryIds = ReturnHeader::where('order_header_id', $value)
            ->where('status', '!=', ReturnHeader::STATUS_CANCELLED)
            ->pluck('delivery_header_id')
            ->toArray();

        $this->dropdown_data['deliveries'] = DeliveryHeader::where('order_header_id', $value)
            ->where('status', 'finished')
            ->whereNotIn('id', $activeReturnDeliveryIds)
            ->orderByDesc('date')
            ->get()
            ->map(fn ($d) => ['value' => $d->id, 'label' => "{$d->code} ({$d->date->format('d M Y')})"])
            ->toArray();
    }

    /**
     * When DO changes, load items.
     */
    public function updatedInputsDeliveryId($value): void
    {
        $this->items = [];
        $this->selectedDelivery = null;

        if (! $value) {
            return;
        }

        $delivery = DeliveryHeader::with([
            'details.item', 'details.itemUom.uom',
        ])->find($value);

        if (! $delivery) {
            return;
        }

        $this->selectedDelivery = $delivery;
        $this->populateItemsFromDelivery();
    }

    protected function populateItemsFromDelivery(): void
    {
        if (! $this->selectedDelivery) {
            return;
        }

        $order = $this->selectedOrder;
        $taxMode = $order->tax_mode ?? 'NONE';
        $taxRate = (float) ($order->tax_rate ?? 0);

        $this->items = $this->selectedDelivery->details->map(function ($detail) use ($taxMode, $taxRate) {
            $maxQty = (float) $detail->quantity_received;
            $price = (float) $detail->price;
            $discount = (float) $detail->discount;

            $calc = TransactionHelper::calculateItemTax($maxQty, $price, $discount, $taxMode, $taxRate);

            return [
                'delivery_detail_id' => $detail->id,
                'item_id' => $detail->item_id,
                'item_uom_id' => $detail->item_uom_id,
                'item_code' => $detail->item?->code ?? '',
                'item_name' => $detail->item?->name ?? '',
                'uom_name' => $detail->itemUom?->uom?->name ?? '',
                'max_quantity' => $maxQty,
                'quantity_return' => $maxQty,
                'price' => $price,
                'discount' => $discount,
                'tax' => $calc['tax'],
                'total' => $calc['total'],
                'include' => true,
            ];
        })->toArray();
    }

    /**
     * Recalculate a line item's tax/total when quantity changes.
     */
    public function updatedItems($value, $key): void
    {
        // key is like "0.quantity_return"
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

        $taxMode = $this->selectedOrder->tax_mode ?? 'NONE';
        $taxRate = (float) ($this->selectedOrder->tax_rate ?? 0);

        $calc = TransactionHelper::calculateItemTax($qty, (float) $line['price'], (float) $line['discount'], $taxMode, $taxRate);
        $line['tax'] = $calc['tax'];
        $line['total'] = $calc['total'];
    }

    public function store(): void
    {
        $this->validate();

        // Must have at least one item included with qty > 0
        $activeItems = collect($this->items)->filter(fn ($item) => $item['include'] && (float) $item['quantity_return'] > 0);

        if ($activeItems->isEmpty()) {
            Flux::toast('At least one item with quantity > 0 is required.', variant: 'danger', position: 'top-end');

            return;
        }

        // Guard: no existing active return for this DO
        $existingReturn = ReturnHeader::where('delivery_header_id', $this->inputs['delivery_id'])
            ->where('status', '!=', ReturnHeader::STATUS_CANCELLED)
            ->exists();

        if ($existingReturn) {
            Flux::toast('A return already exists for this delivery order.', variant: 'danger', position: 'top-end');

            return;
        }

        try {
            DB::transaction(function () use ($activeItems) {
                $order = $this->selectedOrder;

                // Find AR invoice for this DO
                $arInvoice = \App\Models\CMW\Transaction\ArInvoiceHeader::where('delivery_header_id', $this->inputs['delivery_id'])->first();

                $header = ReturnHeader::create([
                    'code' => CodeGeneratorHelper::generateReturnCode(),
                    'transaction_type' => 'SO',
                    'return_type' => $this->inputs['return_type'],
                    'date' => $this->inputs['date'],
                    'order_header_id' => $order->id,
                    'delivery_header_id' => $this->inputs['delivery_id'],
                    'ar_invoice_header_id' => $arInvoice?->id,
                    'partner_id' => $order->partner_id,
                    'company_id' => $order->company_id,
                    'currency_id' => $order->currency_id,
                    'status' => ReturnHeader::STATUS_INIT,
                    'remarks' => $this->inputs['remarks'] ?? null,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);

                $totalSubtotal = 0;
                $totalTax = 0;
                $grandTotal = 0;

                foreach ($activeItems as $item) {
                    $detail = ReturnDetail::create([
                        'return_header_id' => $header->id,
                        'delivery_detail_id' => $item['delivery_detail_id'],
                        'item_id' => $item['item_id'],
                        'item_uom_id' => $item['item_uom_id'],
                        'quantity_return' => $item['quantity_return'],
                        'price' => $item['price'],
                        'discount' => $item['discount'],
                        'tax' => $item['tax'],
                        'total' => $item['total'],
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]);

                    $totalSubtotal += (float) $item['total'] - (float) $item['tax'];
                    $totalTax += (float) $item['tax'];
                    $grandTotal += (float) $item['total'];
                }

                $header->update([
                    'subtotal' => round($totalSubtotal, 2),
                    'tax' => round($totalTax, 2),
                    'total' => round($grandTotal, 2),
                ]);

                Flux::toast("Sales return {$header->code} created successfully.", variant: 'success', position: 'top-end');
                $this->dispatch('sales.return.refresh.draft');
                $this->redirectRoute('sales.return.edit', ['id' => $header->id], navigate: true);
            });
        } catch (\Exception $e) {
            Flux::toast('Error creating sales return: '.$e->getMessage(), variant: 'danger', position: 'top-end');
        }
    }

    public function render()
    {
        return view('livewire.sales.return.create');
    }
}
