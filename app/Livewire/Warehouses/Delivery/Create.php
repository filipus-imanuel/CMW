<?php

namespace App\Livewire\Warehouses\Delivery;

use App\Helpers\CMW\CodeGeneratorHelper;
use App\Helpers\CMW\PopulateDataHelper;
use App\Helpers\CMW\TransactionHelper;
use App\Models\CMW\Inventory\InventoryLedger;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Transaction\DeliveryDetail;
use App\Models\CMW\Transaction\DeliveryHeader;
use App\Models\CMW\Transaction\OrderHeader;
use App\Models\CMW\Transaction\ReturnDetail;
use App\Models\CMW\Transaction\ReturnHeader;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Delivery Order')]
class Create extends Component
{
    public $inputs = [];

    public $lines = [];

    public $order = null;

    public $dropdown_warehouses = [];

    public $dropdown_addresses = [];

    public function rules(): array
    {
        $rules = [
            'inputs.date' => 'required|date',
            'inputs.remarks' => 'nullable|string|max:1024',
            'inputs.delivery_address' => 'nullable|string|max:1024',
            'lines' => 'required|array|min:1',
            'lines.*.warehouse_id' => 'required|exists:warehouses,id',
            'lines.*.quantity_sent' => 'required|numeric|min:0.01',
        ];

        // Validate quantity_sent does not exceed remaining or available stock
        foreach ($this->lines as $i => $line) {
            $maxQty = (float) ($line['quantity_remaining'] ?? 0);
            if ($line['quantity_available'] !== null) {
                $maxQty = min($maxQty, (float) $line['quantity_available']);
            }
            if ($maxQty > 0) {
                $rules["lines.{$i}.quantity_sent"] = "required|numeric|min:0.01|max:{$maxQty}";
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'lines.required' => 'At least one line item is required.',
            'lines.min' => 'At least one line item is required.',
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'inputs.date' => 'delivery date',
            'lines' => 'line items',
            'lines.*.warehouse_id' => 'warehouse',
            'lines.*.quantity_sent' => 'quantity to send',
        ];
    }

    public function mount($orderId): void
    {
        $this->authorize('create delivery order');

        $this->order = OrderHeader::with([
            'partner', 'company', 'currency', 'itemCategory',
            'details.item', 'details.itemUom.uom', 'details.deliveryDetails',
        ])->findOrFail($orderId);

        // Guard: only ORDER or DELIVERY status
        if (! in_array($this->order->status, ['ORDER', 'DELIVERY'])) {
            Flux::toast('This sales order is not available for delivery.', variant: 'danger', position: 'top right');
            $this->redirectRoute('warehouses.delivery.upcoming', navigate: true);

            return;
        }

        // Load partner addresses for dropdown
        $this->loadPartnerAddresses();

        // Auto-select default address
        $defaultAddress = $this->order->partner?->addresses()
            ->where('is_default', true)
            ->first();

        $deliveryAddress = '';
        $selectedAddressId = '';
        if ($defaultAddress) {
            $selectedAddressId = (string) $defaultAddress->id;
            $parts = array_filter([
                $defaultAddress->address,
                $defaultAddress->city,
            ]);
            $deliveryAddress = implode(', ', $parts);
        }

        $this->inputs = [
            'date' => now()->format('Y-m-d'),
            'order_header_id' => $this->order->id,
            'remarks' => '',
            'selected_address_id' => $selectedAddressId,
            'delivery_address' => $deliveryAddress,
            'partner_name' => $this->order->partner?->name ?? '-',
            'company_name' => $this->order->company?->name ?? '-',
            'currency_code' => $this->order->currency?->code ?? '-',
        ];

        $this->populateLines();
        $this->loadDropdowns();
    }

    private function loadPartnerAddresses(): void
    {
        $this->dropdown_addresses = [];

        if (! $this->order->partner) {
            return;
        }

        $addresses = $this->order->partner->addresses()
            ->orderByDesc('is_default')
            ->get();

        foreach ($addresses as $addr) {
            $parts = array_filter([$addr->address, $addr->city]);
            $label = $addr->label ? $addr->label.': '.implode(', ', $parts) : implode(', ', $parts);

            $this->dropdown_addresses[] = [
                'value' => (string) $addr->id,
                'label' => $label,
                'address_text' => implode(', ', $parts),
            ];
        }
    }

    public function updatedInputsSelectedAddressId($value): void
    {
        if (empty($value)) {
            return;
        }

        $selected = collect($this->dropdown_addresses)->firstWhere('value', (string) $value);

        if ($selected) {
            $this->inputs['delivery_address'] = $selected['address_text'];
        }
    }

    private function populateLines(): void
    {
        $this->lines = [];

        foreach ($this->order->details as $detail) {
            // Calculate already delivered quantity (non-cancelled deliveries)
            $deliveredQty = $detail->deliveryDetails()
                ->whereHas('header', fn ($q) => $q->whereIn('status', ['ongoing', 'finished']))
                ->sum('quantity_sent');

            // Add back redelivery quantities from finished ITEM-type returns
            $redeliveryQty = ReturnDetail::whereHas('deliveryDetail', function ($q) use ($detail) {
                $q->where('order_detail_id', $detail->id);
            })
                ->whereHas('header', function ($q) {
                    $q->where('status', ReturnHeader::STATUS_FINISH)
                        ->where('return_type', 'ITEM');
                })
                ->sum('quantity_redelivery');

            $remaining = (float) $detail->quantity - (float) $deliveredQty + (float) $redeliveryQty;

            $this->lines[] = [
                'order_detail_id' => $detail->id,
                'item_id' => $detail->item_id,
                'item_name' => $detail->item?->code.' - '.$detail->item?->name,
                'item_uom_id' => $detail->item_uom_id,
                'uom_name' => $detail->itemUom?->uom?->name ?? '-',
                'quantity_ordered' => number_format((float) $detail->quantity, 2),
                'quantity_delivered' => number_format((float) $deliveredQty, 2),
                'quantity_remaining' => $remaining,
                'quantity_sent' => $remaining > 0 ? number_format($remaining, 2) : '0.00',
                'price' => (float) $detail->price_deal > 0 ? (float) $detail->price_deal : (float) $detail->price_proposed,
                'discount' => (float) $detail->discount,
                'warehouse_id' => '',
                'warehouse_options' => [],
                'quantity_available' => null,
                'enabled' => $remaining > 0,
            ];
        }

        // Load warehouse options for each item
        foreach ($this->lines as $i => $line) {
            $this->lines[$i]['warehouse_options'] = $this->getWarehouseOptionsForItem($line['item_id']);

            // Auto-select if only one warehouse option
            if (count($this->lines[$i]['warehouse_options']) === 1) {
                $this->lines[$i]['warehouse_id'] = $this->lines[$i]['warehouse_options'][0]['value'];
                $this->lines[$i]['quantity_available'] = TransactionHelper::getAvailableStock(
                    (int) $line['item_id'],
                    (int) $this->lines[$i]['warehouse_id'],
                    $line['item_uom_id'] ? (int) $line['item_uom_id'] : null
                );
            }
        }
    }

    /**
     * React to line field changes — update available qty when warehouse changes.
     */
    public function updatedLines($value, string $key): void
    {
        // key format: "0.warehouse_id"
        if (str_ends_with($key, '.warehouse_id')) {
            $index = (int) explode('.', $key)[0];

            if (! empty($value) && isset($this->lines[$index])) {
                $line = $this->lines[$index];
                $this->lines[$index]['quantity_available'] = TransactionHelper::getAvailableStock(
                    (int) $line['item_id'],
                    (int) $value,
                    $line['item_uom_id'] ? (int) $line['item_uom_id'] : null
                );
            } elseif (isset($this->lines[$index])) {
                $this->lines[$index]['quantity_available'] = null;
            }
        }
    }

    private function getWarehouseOptionsForItem(int $itemId): array
    {
        // Get warehouses assigned to this item, filtered by order's company
        $companyId = $this->order->company_id;

        if ($companyId) {
            // Try: warehouses that belong to the company AND are assigned to the item
            $warehouses = DB::table('item_warehouses')
                ->join('warehouses', 'warehouses.id', '=', 'item_warehouses.warehouse_id')
                ->join('company_warehouses', 'company_warehouses.warehouse_id', '=', 'warehouses.id')
                ->where('item_warehouses.item_id', $itemId)
                ->where('company_warehouses.company_id', $companyId)
                ->where('warehouses.is_active', true)
                ->whereNull('item_warehouses.deleted_at')
                ->select('warehouses.id as value', DB::raw("CONCAT(warehouses.code, ' - ', warehouses.name) as label"))
                ->get()
                ->toArray();

            if (! empty($warehouses)) {
                return array_map(fn ($w) => ['value' => $w->value, 'label' => $w->label], $warehouses);
            }
        }

        // Fallback: warehouses assigned to this item (regardless of company)
        return PopulateDataHelper::getWarehousesByItem($itemId);
    }

    private function loadDropdowns(): void
    {
        $this->dropdown_warehouses = PopulateDataHelper::getWarehouses(['useCache' => false]);
    }

    public function store(): void
    {
        $this->authorize('create delivery order');

        // Filter out disabled lines (no remaining qty)
        $activeLines = array_filter($this->lines, fn ($line) => $line['enabled'] && (float) $line['quantity_sent'] > 0);

        if (empty($activeLines)) {
            Flux::toast('No items to deliver. All quantities have been fulfilled.', variant: 'danger', position: 'top right');

            return;
        }

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($activeLines) {
                $order = OrderHeader::lockForUpdate()->findOrFail($this->order->id);

                // Validate stock availability for each line
                foreach ($activeLines as $i => $line) {
                    $quantitySent = (float) $line['quantity_sent'];
                    $warehouseId = (int) $line['warehouse_id'];
                    $itemId = (int) $line['item_id'];
                    $itemUomId = $line['item_uom_id'] ? (int) $line['item_uom_id'] : null;

                    // Convert to base UOM for stock check
                    $baseQty = TransactionHelper::convertToBaseUom($quantitySent, $itemUomId);
                    $currentBalance = TransactionHelper::getWarehouseBalance($itemId, $warehouseId);

                    if ($currentBalance < $baseQty) {
                        $itemName = $line['item_name'];
                        throw new Exception("Insufficient stock for {$itemName} in selected warehouse. Available: {$currentBalance}, Required: {$baseQty}");
                    }
                }

                // Calculate tax per line using TransactionHelper
                $taxMode = $order->tax_mode ?? 'NONE';
                $taxRate = (float) ($order->tax_rate ?? 0);

                $totalSubtotal = 0;
                $totalTax = 0;
                $grandTotal = 0;

                $detailsData = [];
                foreach ($activeLines as $line) {
                    $quantitySent = (float) $line['quantity_sent'];
                    $price = (float) $line['price'];
                    $discount = (float) $line['discount'];

                    // Proportional discount based on quantity ratio
                    $orderDetailQty = DB::table('order_details')
                        ->where('id', $line['order_detail_id'])
                        ->value('quantity');
                    $proportionalDiscount = $orderDetailQty > 0
                        ? round($discount * ($quantitySent / (float) $orderDetailQty), 2)
                        : 0;

                    $calc = TransactionHelper::calculateItemTax(
                        $quantitySent,
                        $price,
                        $proportionalDiscount,
                        $taxMode,
                        $taxRate
                    );

                    $totalSubtotal += $calc['subtotal'];
                    $totalTax += $calc['tax'];
                    $grandTotal += $calc['total'];

                    $detailsData[] = [
                        'line' => $line,
                        'quantity_sent' => $quantitySent,
                        'proportional_discount' => $proportionalDiscount,
                        'tax' => $calc['tax'],
                        'total' => $calc['total'],
                    ];
                }

                // Create delivery header
                $header = DeliveryHeader::create([
                    'code' => CodeGeneratorHelper::generateDeliveryCode(),
                    'date' => $this->inputs['date'],
                    'order_header_id' => $order->id,
                    'partner_id' => $order->partner_id,
                    'company_id' => $order->company_id,
                    'currency_id' => $order->currency_id,
                    'status' => DeliveryHeader::STATUS_ONGOING,
                    'subtotal' => round($totalSubtotal, 2),
                    'tax' => round($totalTax, 2),
                    'total' => round($grandTotal, 2),
                    'remarks' => $this->inputs['remarks'] ?? null,
                    'delivery_address' => $this->inputs['delivery_address'] ?? null,
                    'created_by' => Auth::id(),
                ]);

                // Create delivery details and deduct stock
                foreach ($detailsData as $data) {
                    $line = $data['line'];

                    DeliveryDetail::create([
                        'delivery_header_id' => $header->id,
                        'order_detail_id' => $line['order_detail_id'],
                        'item_id' => $line['item_id'],
                        'item_uom_id' => $line['item_uom_id'],
                        'warehouse_id' => $line['warehouse_id'],
                        'quantity_sent' => $data['quantity_sent'],
                        'quantity_received' => 0,
                        'price' => $line['price'],
                        'discount' => $data['proportional_discount'],
                        'tax' => $data['tax'],
                        'total' => $data['total'],
                        'created_by' => Auth::id(),
                    ]);

                    // Deduct stock via inventory ledger
                    $itemUomId = $line['item_uom_id'] ? (int) $line['item_uom_id'] : null;
                    $baseQty = TransactionHelper::convertToBaseUom($data['quantity_sent'], $itemUomId);
                    $currentBalance = TransactionHelper::getWarehouseBalance((int) $line['item_id'], (int) $line['warehouse_id']);

                    $unitCost = (float) (Item::where('id', $line['item_id'])->value('cost_price') ?? 0);

                    InventoryLedger::create([
                        'item_id' => $line['item_id'],
                        'warehouse_id' => $line['warehouse_id'],
                        'date' => $this->inputs['date'],
                        'type' => 'sales',
                        'reference_type' => DeliveryHeader::class,
                        'reference_id' => $header->id,
                        'quantity_in' => 0,
                        'quantity_out' => $baseQty,
                        'balance' => $currentBalance - $baseQty,
                        'unit_cost' => $unitCost,
                        'remarks' => 'Delivery Order: '.$header->code,
                        'created_by' => Auth::id(),
                    ]);
                }

                // Update SO status to DELIVERY if first delivery
                if ($order->status === 'ORDER') {
                    $order->update([
                        'status' => 'DELIVERY',
                        'updated_by' => Auth::id(),
                    ]);
                }

                Flux::toast('Delivery order created successfully: '.$header->code, variant: 'success', position: 'top right');
                $this->dispatch('shp.warehouse.delivery.created');
                $this->redirectRoute('warehouses.delivery.ongoing.show', ['id' => $header->id], navigate: true);
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors.', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('Database error occurred while creating delivery order.', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast($e->getMessage(), variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.warehouses.delivery.create');
    }
}
