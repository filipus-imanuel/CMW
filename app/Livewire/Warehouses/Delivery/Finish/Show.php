<?php

namespace App\Livewire\Warehouses\Delivery\Finish;

use App\Models\CMW\Transaction\DeliveryHeader;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Finished Delivery Detail')]
class Show extends Component
{
    public ?DeliveryHeader $header = null;

    public function mount($id): void
    {
        $this->authorize('view delivery order');

        $this->header = DeliveryHeader::with([
            'orderHeader', 'partner', 'company', 'currency',
            'details.item', 'details.itemUom.uom', 'details.warehouse',
            'createdBy', 'confirmedByUser', 'arInvoices', 'inventoryLedgers',
            'returns',
        ])->finished()->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.warehouses.delivery.finish.show');
    }
}
