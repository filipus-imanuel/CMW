<?php

namespace App\Livewire\Partners\SupplierAddresses;

use App\Helpers\CMW\PopulateDataHelper;
use App\Models\CMW\Master\Partner;
use App\Models\CMW\Master\PartnerAddress;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Supplier Address')]
class Create extends Component
{
    public $inputs = [];

    public $dropdown_supplier = [];

    public function rules()
    {
        return [
            'inputs.partner_id' => 'required|exists:partners,id',
            'inputs.label' => 'required|string|max:100',
            'inputs.address' => 'required|string|max:500',
            'inputs.city' => 'nullable|string|max:100',
            'inputs.phone' => 'nullable|string|max:20',
            'inputs.contact_person' => 'nullable|string|max:100',
            'inputs.is_default' => 'boolean',
            'inputs.remarks' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'inputs.partner_id.required' => 'Supplier is required',
            'inputs.label.required' => 'Label is required',
            'inputs.address.required' => 'Address is required',
        ];
    }

    private function handlePopulateSupplier(): void
    {
        $this->dropdown_supplier = PopulateDataHelper::getSuppliers(['labelFormat' => 'name_code']);

        // Set default value to first item (index 0 since no prependDefault) if not already set
        if (! isset($this->inputs['partner_id'])) {
            $this->inputs['partner_id'] = $this->dropdown_supplier[0]['value'] ?? null;
        }
    }

    #[On('cmw.partners.supplier-addresses.create.open')]
    public function openModal($partnerId = null)
    {
        $this->authorize('create partner address');

        $this->reset(['inputs']);

        if ($partnerId) {
            $this->inputs['partner_id'] = $partnerId;
        }

        $this->inputs['is_default'] = false;

        $this->resetValidation();
        $this->handlePopulateSupplier();

        $this->modal('create-supplier-address')->show();
    }

    public function save()
    {
        $this->authorize('create partner address');

        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            // If this is default, unset other defaults for same partner
            if ($validated['inputs']['is_default']) {
                PartnerAddress::where('partner_id', $validated['inputs']['partner_id'])
                    ->update(['is_default' => false]);
            }

            PartnerAddress::create([
                ...$validated['inputs'],
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);
        });

        Flux::toast('Supplier address created successfully', variant: 'success', position: 'top-end');

        $this->dispatch('cmw.partners.supplier-addresses.refresh');
        $this->modal('create-supplier-address')->close();
    }

    public function render()
    {
        return view('livewire.partners.supplier-addresses.create');
    }
}
