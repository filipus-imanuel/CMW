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

    public string $cancellation_reason = '';

    public function mount($id): void
    {
        $this->authorize('view sales order');

        $this->loadOrder($id);

        // Guard: allow INIT, ORDER, DELIVERY, FINISH, REJECTED, or CANCELLED status
        if (! in_array($this->order->status, ['INIT', 'ORDER', 'DELIVERY', 'FINISH', 'REJECTED', 'CANCELLED'])) {
            $this->redirectRoute('sales.order.index.ongoing', navigate: true);

            return;
        }
    }

    /**
     * Reload the order with all relations so the page reflects the latest server state.
     */
    public function refreshOrder(): void
    {
        $this->loadOrder($this->order->id);

        Flux::toast('Order refreshed.', variant: 'success', position: 'top-end');
    }

    protected function loadOrder(int $id): void
    {
        $this->order = OrderHeader::with([
            'partner', 'company', 'itemCategory', 'currency',
            'details.item', 'details.itemUom.uom',
            'details.deliveryDetails.header',
            'createdBy', 'approvedByUser',
            'deliveries.returns',
            'returns.deliveryHeader',
            'deliverySchedules',
        ])->findOrFail($id);
    }

    /**
     * Cancel an INIT or ORDER status order that has no delivery orders.
     */
    public function cancelOrder(): void
    {
        $this->authorize('edit sales order');

        if (! in_array($this->order->status, ['INIT', 'ORDER'])) {
            Flux::toast('Only draft or ongoing orders can be cancelled.', variant: 'danger', position: 'top-end');

            return;
        }

        if ($this->order->deliveries()->exists()) {
            Flux::toast('Cannot cancel — this order has delivery orders.', variant: 'danger', position: 'top-end');

            return;
        }

        $this->validate([
            'cancellation_reason' => 'required|string|max:1024',
        ]);

        try {
            DB::transaction(function () {
                $this->order->update([
                    'status' => 'CANCELLED',
                    'rejection_reason' => $this->cancellation_reason,
                    'updated_by' => Auth::id(),
                ]);

                $this->dispatch('shp.sales.order.refresh.cancelled');
            });

            Flux::toast("Order {$this->order->code_request} cancelled.", variant: 'success', position: 'top-end');
            $this->redirectRoute('sales.order.index.cancelled', navigate: true);
        } catch (\Exception $e) {
            Flux::toast('Error: '.$e->getMessage(), variant: 'danger', position: 'top-end');
        }
    }

    /**
     * Replicate a REJECTED or CANCELLED order back to a new Sales Request (INIT).
     * Copies header fields and details with price_proposed from the SO's price_deal.
     */
    public function replicateToRequest(): void
    {
        $this->authorize('create sales request');

        if (! in_array($this->order->status, ['REJECTED', 'CANCELLED'])) {
            Flux::toast('Only rejected or cancelled orders can be replicated.', variant: 'danger', position: 'top-end');

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
