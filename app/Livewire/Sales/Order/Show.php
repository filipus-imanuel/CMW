<?php

namespace App\Livewire\Sales\Order;

use App\Helpers\CMW\CodeGeneratorHelper;
use App\Models\CMW\Transaction\OrderDetail;
use App\Models\CMW\Transaction\OrderHeader;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Sales Order Detail')]
class Show extends Component
{
    public ?OrderHeader $order = null;

    public function mount($id): void
    {
        $this->authorize('view sales order');

        $this->order = OrderHeader::with([
            'partner', 'company', 'itemCategory', 'currency',
            'details.item', 'details.itemUom.uom',
            'createdBy', 'approvedByUser',
        ])->findOrFail($id);

        // Guard: only ORDER or REJECTED status
        if (! in_array($this->order->status, ['ORDER', 'REJECTED'])) {
            $this->redirectRoute('sales.order.index.ongoing', navigate: true);

            return;
        }
    }

    /**
     * Replicate a REJECTED order back to a new Sales Request (INIT).
     * Copies header fields and details with price_proposed from the SO's price_deal.
     */
    public function replicateToRequest(): void
    {
        $this->authorize('create sales request');

        if ($this->order->status !== 'REJECTED') {
            Flux::toast('Only rejected orders can be replicated.', variant: 'danger', position: 'top-end');

            return;
        }

        DB::transaction(function () {
            $newHeader = OrderHeader::create([
                'code_request' => CodeGeneratorHelper::generateOrderCode('SR'),
                'date' => now(),
                'delivery_date' => $this->order->delivery_date,
                'currency_id' => $this->order->currency_id,
                'partner_id' => $this->order->partner_id,
                'company_id' => $this->order->company_id,
                'item_category_id' => $this->order->item_category_id,
                'tax_mode' => $this->order->tax_mode,
                'tax_id' => $this->order->tax_id,
                'tax_rate' => $this->order->tax_rate,
                'status' => 'INIT',
                'subtotal' => $this->order->subtotal,
                'discount' => $this->order->discount,
                'tax' => $this->order->tax,
                'total' => $this->order->total,
                'remarks' => '[Replicated from rejected '.$this->order->code_request.']',
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            foreach ($this->order->details as $detail) {
                OrderDetail::create([
                    'order_header_id' => $newHeader->id,
                    'item_id' => $detail->item_id,
                    'item_uom_id' => $detail->item_uom_id,
                    'quantity' => $detail->quantity,
                    'price_proposed' => $detail->price_deal ?: $detail->price_proposed,
                    'price_deal' => 0,
                    'discount' => $detail->discount,
                    'tax' => $detail->tax,
                    'total' => $detail->total,
                    'remarks' => $detail->remarks,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }

            Flux::toast('New sales request created from rejected order: '.$newHeader->code_request, variant: 'success', position: 'top-end');
            $this->dispatch('sales.request.refresh.init');
            $this->redirectRoute('sales.request.edit', ['id' => $newHeader->id], navigate: true);
        });
    }

    public function render()
    {
        return view('livewire.sales.order.show');
    }
}
