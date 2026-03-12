<?php

namespace App\Livewire\Warehouses\Delivery\Upcoming;

use App\Models\CMW\Transaction\OrderHeader;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Sales Order — Warehouse Delivery')]
class Show extends Component
{
    public ?OrderHeader $order = null;

    public $lines = [];

    public function mount($id): void
    {
        $this->authorize('view delivery order');

        $this->order = OrderHeader::with([
            'partner', 'company', 'currency', 'itemCategory',
            'details.item', 'details.itemUom.uom', 'details.deliveryDetails',
            'details.deliverySchedules',
            'deliveries.createdBy',
            'createdBy', 'approvedByUser',
        ])->whereIn('status', ['ORDER', 'DELIVERY'])->findOrFail($id);

        $this->populateLines();
    }

    private function populateLines(): void
    {
        $this->lines = [];

        foreach ($this->order->details as $detail) {
            $deliveredQty = $detail->deliveryDetails()
                ->whereHas('header', fn ($q) => $q->whereIn('status', ['ongoing', 'finished']))
                ->sum('quantity_sent');

            $remaining = (float) $detail->quantity - (float) $deliveredQty;

            $this->lines[] = [
                'item_code' => $detail->item?->code,
                'item_name' => $detail->item?->name,
                'uom_name' => $detail->itemUom?->uom?->name ?? '-',
                'quantity_ordered' => (float) $detail->quantity,
                'quantity_delivered' => (float) $deliveredQty,
                'quantity_remaining' => $remaining,
                'price' => (float) $detail->price_deal > 0 ? (float) $detail->price_deal : (float) $detail->price_proposed,
                'discount' => (float) $detail->discount,
                'tax' => (float) $detail->tax,
                'total' => (float) $detail->total,
            ];
        }
    }

    public function render()
    {
        return view('livewire.warehouses.delivery.upcoming.show');
    }
}
