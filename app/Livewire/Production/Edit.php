<?php

namespace App\Livewire\Production;

use App\Models\CMW\Transaction\OrderHeader;
use App\Models\CMW\Transaction\StockAdjustmentHeader;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Production')]
class Edit extends Component
{
    public ?OrderHeader $order = null;

    public string $production_status = OrderHeader::PRODUCTION_ONGOING;

    public ?string $work_order_manual = null;

    public ?string $production_date = null;

    public bool $readOnly = false;

    public function rules(): array
    {
        return [
            'production_status' => ['required', Rule::in([OrderHeader::PRODUCTION_ONGOING, OrderHeader::PRODUCTION_FINISH])],
            'work_order_manual' => ['nullable', 'string', 'max:100'],
            'production_date' => ['nullable', 'date'],
        ];
    }

    public function mount($id): void
    {
        $this->authorize('edit production order');

        $this->order = OrderHeader::with(['partner', 'itemCategory', 'details.item', 'details.itemUom.uom'])->findOrFail($id);

        if (! in_array($this->order->status, ['ORDER', 'DELIVERY'])) {
            Flux::toast('Production status can only be edited while SO is ORDER or DELIVERY.', variant: 'danger', position: 'top-end');
            $this->redirectRoute('production.index', navigate: true);

            return;
        }

        $this->production_status = $this->order->production_status ?? OrderHeader::PRODUCTION_ONGOING;
        $this->work_order_manual = $this->order->work_order_manual;
        $this->production_date = $this->order->production_date?->format('Y-m-d');
        $this->readOnly = $this->production_status === OrderHeader::PRODUCTION_FINISH;
    }

    public function save(): void
    {
        $this->authorize('edit production order');

        if (! in_array($this->order->status, ['ORDER', 'DELIVERY'])) {
            Flux::toast('Production status can only be edited while SO is ORDER or DELIVERY.', variant: 'danger', position: 'top-end');

            return;
        }

        if (($this->order->production_status ?? OrderHeader::PRODUCTION_ONGOING) === OrderHeader::PRODUCTION_FINISH) {
            Flux::toast('Production has already finished — fields are no longer editable.', variant: 'danger', position: 'top-end');

            return;
        }

        $this->validate();

        DB::transaction(function () {
            $this->order->update([
                'production_status' => $this->production_status,
                'work_order_manual' => $this->work_order_manual,
                'production_date' => $this->production_date ?: null,
                'updated_by' => Auth::id(),
            ]);

            StockAdjustmentHeader::where('order_header_id', $this->order->id)
                ->update([
                    'work_order_manual' => $this->work_order_manual,
                    'production_date' => $this->production_date ?: null,
                    'updated_by' => Auth::id(),
                ]);
        });

        Flux::toast('Production updated.', variant: 'success', position: 'top-end');
        $this->dispatch('shp.production.order.refresh');
        $this->redirectRoute('production.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.production.edit');
    }
}
