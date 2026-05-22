<?php

namespace App\Livewire\Production;

use App\Models\CMW\Transaction\OrderHeader;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Production Status')]
class Edit extends Component
{
    public ?OrderHeader $order = null;

    public string $production_status = OrderHeader::PRODUCTION_ONGOING;

    public function rules(): array
    {
        return [
            'production_status' => ['required', Rule::in([OrderHeader::PRODUCTION_ONGOING, OrderHeader::PRODUCTION_FINISH])],
        ];
    }

    public function mount($id): void
    {
        $this->authorize('edit production order');

        $this->order = OrderHeader::with(['partner', 'itemCategory'])->findOrFail($id);

        if (! in_array($this->order->status, ['ORDER', 'DELIVERY'])) {
            Flux::toast('Production status can only be edited while SO is ORDER or DELIVERY.', variant: 'danger', position: 'top-end');
            $this->redirectRoute('production.index', navigate: true);

            return;
        }

        $this->production_status = $this->order->production_status ?? OrderHeader::PRODUCTION_ONGOING;
    }

    public function save(): void
    {
        $this->authorize('edit production order');

        if (! in_array($this->order->status, ['ORDER', 'DELIVERY'])) {
            Flux::toast('Production status can only be edited while SO is ORDER or DELIVERY.', variant: 'danger', position: 'top-end');

            return;
        }

        $this->validate();

        $this->order->update([
            'production_status' => $this->production_status,
            'updated_by' => Auth::id(),
        ]);

        Flux::toast('Production status updated.', variant: 'success', position: 'top-end');
        $this->dispatch('shp.production.order.refresh');
        $this->redirectRoute('production.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.production.edit');
    }
}
