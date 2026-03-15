<?php

namespace App\Livewire\Sales\Payment;

use App\Helpers\CMW\CodeGeneratorHelper;
use App\Helpers\CMW\TransactionHelper;
use App\Models\CMW\Master\PaymentMethod;
use App\Models\CMW\Transaction\ArInvoiceHeader;
use App\Models\CMW\Transaction\ArPaymentDetail;
use App\Models\CMW\Transaction\ArPaymentHeader;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Record Payment')]
class Create extends Component
{
    public ?ArInvoiceHeader $invoice = null;

    public array $inputs = [
        'date' => '',
        'amount' => '',
        'payment_method_id' => '',
        'reference' => '',
        'remarks' => '',
    ];

    public function mount($invoiceId): void
    {
        $this->authorize('create ar payment');

        $this->invoice = ArInvoiceHeader::with(['partner', 'currency', 'orderHeader.company'])
            ->findOrFail($invoiceId);

        if ($this->invoice->isPaid()) {
            Flux::toast('This invoice is already fully paid.', variant: 'danger', position: 'top-end');
            $this->redirectRoute('sales.invoice.show', ['id' => $this->invoice->id], navigate: true);

            return;
        }

        $this->inputs['date'] = now()->format('Y-m-d');
        $this->inputs['amount'] = $this->invoice->balance;
    }

    public function store(): void
    {
        $this->authorize('create ar payment');

        $this->validate([
            'inputs.date' => 'required|date',
            'inputs.amount' => 'required|numeric|min:0.01|max:'.$this->invoice->balance,
            'inputs.payment_method_id' => 'required|exists:payment_methods,id',
            'inputs.reference' => 'nullable|string|max:255',
            'inputs.remarks' => 'nullable|string|max:1024',
        ]);

        try {
            DB::transaction(function () {
                $invoice = ArInvoiceHeader::lockForUpdate()->with('orderHeader')->findOrFail($this->invoice->id);

                $amount = (float) $this->inputs['amount'];

                if ($amount > (float) $invoice->balance) {
                    throw new \Exception('Payment amount exceeds outstanding balance.');
                }

                $companyId = $invoice->orderHeader?->company_id;
                if (! $companyId) {
                    throw new \Exception('Cannot determine company for payment code generation.');
                }

                $paymentHeader = ArPaymentHeader::create([
                    'code' => CodeGeneratorHelper::generatePaymentCode($companyId),
                    'date' => $this->inputs['date'],
                    'currency_id' => $invoice->currency_id,
                    'partner_id' => $invoice->partner_id,
                    'company_id' => $companyId,
                    'amount' => $amount,
                    'payment_method_id' => $this->inputs['payment_method_id'],
                    'reference' => $this->inputs['reference'],
                    'remarks' => $this->inputs['remarks'],
                    'status' => ArPaymentHeader::STATUS_ACTIVE,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);

                ArPaymentDetail::create([
                    'ar_payment_header_id' => $paymentHeader->id,
                    'ar_invoice_header_id' => $invoice->id,
                    'amount' => $amount,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);

                $newPaid = (float) $invoice->paid + $amount;
                $newBalance = (float) $invoice->total - $newPaid - (float) $invoice->return_total;
                $newStatus = $newBalance <= 0
                    ? ArInvoiceHeader::STATUS_PAID
                    : ArInvoiceHeader::STATUS_PARTIAL;

                $invoice->update([
                    'paid' => $newPaid,
                    'balance' => max(0, $newBalance),
                    'status' => $newStatus,
                    'updated_by' => Auth::id(),
                ]);

                if ($invoice->order_header_id) {
                    TransactionHelper::checkAndUpdateOrderFinalStatus($invoice->order_header_id);
                }

                $this->dispatch('shp.sales.payment.created');
                $this->dispatch('shp.sales.invoice.refresh.unpaid');
                $this->dispatch('shp.sales.invoice.refresh.paid');

                Flux::toast("Payment {$paymentHeader->code} recorded successfully.", variant: 'success', position: 'top-end');
                $this->redirectRoute('sales.payment.show', ['id' => $paymentHeader->id], navigate: true);
            });
        } catch (\Exception $e) {
            Flux::toast('Error: '.$e->getMessage(), variant: 'danger', position: 'top-end');
        }
    }

    public function render()
    {
        return view('livewire.sales.payment.create', [
            'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
