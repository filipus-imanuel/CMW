<?php

namespace App\Livewire\Sales\Invoice;

use App\Models\CMW\Transaction\ArInvoiceHeader;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Invoice Detail')]
class Show extends Component
{
    public ?ArInvoiceHeader $invoice = null;

    public function mount($id): void
    {
        $this->authorize('view ar invoice');

        $this->invoice = ArInvoiceHeader::with([
            'partner',
            'currency',
            'orderHeader.company',
            'deliveryHeader.details.item',
            'deliveryHeader.details.itemUom.uom',
            'paymentDetails.header.paymentMethod',
            'createdBy',
        ])->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.sales.invoice.show');
    }
}
