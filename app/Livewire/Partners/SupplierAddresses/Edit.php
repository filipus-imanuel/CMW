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

#[Title('Edit Supplier Address')]
class Edit extends Component
{
    public ?PartnerAddress $partnerAddress = null;

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
            'inputs.is_active' => 'boolean',
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
    }

    #[On('cmw.partners.supplier-addresses.edit.open')]
    public function openModal($id)
    {
        $this->authorize('edit partner address');

        $this->partnerAddress = PartnerAddress::findOrFail($id);

        $this->inputs['partner_id'] = $this->partnerAddress->partner_id;
        $this->inputs['label'] = $this->partnerAddress->label;
        $this->inputs['address'] = $this->partnerAddress->address;
        $this->inputs['city'] = $this->partnerAddress->city;
        $this->inputs['phone'] = $this->partnerAddress->phone;
        $this->inputs['contact_person'] = $this->partnerAddress->contact_person;
        $this->inputs['is_default'] = $this->partnerAddress->is_default;
        $this->inputs['remarks'] = $this->partnerAddress->remarks;
        $this->inputs['is_active'] = (bool) $this->partnerAddress->is_active;

        $this->resetValidation();
        $this->handlePopulateSupplier();

        $this->modal('edit-supplier-address')->show();
    }

    public function save()
    {
        $this->authorize('edit partner address');

        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            // If this is default, unset other defaults for same partner
            if ($validated['inputs']['is_default']) {
                PartnerAddress::where('partner_id', $validated['inputs']['partner_id'])
                    ->where('id', '!=', $this->partnerAddress->id)
                    ->update(['is_default' => false]);
            }

            $this->partnerAddress->update([
                ...$validated['inputs'],
                'updated_by' => Auth::id(),
            ]);
        });

        Flux::toast('Supplier address updated successfully', variant: 'success', position: 'top-end');

        $this->dispatch('cmw.partners.supplier-addresses.refresh');
        $this->modal('edit-supplier-address')->close();
    }

    public function render()
    {
        return view('livewire.partners.supplier-addresses.edit');
    }
}
