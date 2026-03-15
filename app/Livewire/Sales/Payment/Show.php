<?php

namespace App\Livewire\Sales\Payment;

use App\Helpers\CMW\TransactionHelper;
use App\Models\CMW\Transaction\ArInvoiceHeader;
use App\Models\CMW\Transaction\ArPaymentHeader;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Payment Detail')]
class Show extends Component
{
    public ?ArPaymentHeader $payment = null;

    public string $cancel_reason = '';

    public function mount($id): void
    {
        $this->authorize('view ar payment');

        $this->payment = ArPaymentHeader::with([
            'partner',
            'company',
            'currency',
            'paymentMethod',
            'details.invoice.orderHeader',
            'details.invoice.deliveryHeader',
            'createdBy',
        ])->findOrFail($id);
    }

    public function cancelPayment(): void
    {
        $this->authorize('cancel ar payment');

        if (! $this->payment->isActive()) {
            Flux::toast('This payment is already cancelled.', variant: 'danger', position: 'top-end');

            return;
        }

        $this->validate([
            'cancel_reason' => 'required|string|max:1024',
        ]);

        try {
            DB::transaction(function () {
                $payment = ArPaymentHeader::lockForUpdate()->findOrFail($this->payment->id);

                $payment->update([
                    'status' => ArPaymentHeader::STATUS_CANCELLED,
                    'cancel_reason' => $this->cancel_reason,
                    'updated_by' => Auth::id(),
                ]);

                foreach ($payment->details as $detail) {
                    $invoice = ArInvoiceHeader::lockForUpdate()->findOrFail($detail->ar_invoice_header_id);

                    $newPaid = max(0, (float) $invoice->paid - (float) $detail->amount);
                    $newBalance = (float) $invoice->total - $newPaid;
                    $newStatus = $newPaid <= 0
                        ? ArInvoiceHeader::STATUS_UNPAID
                        : ArInvoiceHeader::STATUS_PARTIAL;

                    $invoice->update([
                        'paid' => $newPaid,
                        'balance' => $newBalance,
                        'status' => $newStatus,
                        'updated_by' => Auth::id(),
                    ]);
                }

                foreach ($payment->details as $detail) {
                    $inv = ArInvoiceHeader::find($detail->ar_invoice_header_id);
                    if ($inv?->order_header_id) {
                        TransactionHelper::checkAndUpdateOrderFinalStatus($inv->order_header_id);
                    }
                }

                $this->dispatch('shp.sales.payment.refresh.active');
                $this->dispatch('shp.sales.payment.refresh.cancelled');
                $this->dispatch('shp.sales.invoice.refresh.unpaid');
                $this->dispatch('shp.sales.invoice.refresh.paid');

                Flux::toast("Payment {$payment->code} cancelled.", variant: 'success', position: 'top-end');
                $this->redirectRoute('sales.payment.index.cancelled', navigate: true);
            });
        } catch (\Exception $e) {
            Flux::toast('Error: '.$e->getMessage(), variant: 'danger', position: 'top-end');
        }
    }

    public function render()
    {
        return view('livewire.sales.payment.show');
    }
}
