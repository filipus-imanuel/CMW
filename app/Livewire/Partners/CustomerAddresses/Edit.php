<?php

namespace App\Livewire\Partners\CustomerAddresses;

use App\Models\CMW\Master\Partner;
use App\Models\CMW\Master\PartnerAddress;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Customer Address')]
class Edit extends Component
{
    public ?PartnerAddress $partnerAddress = null;

    public $inputs = [];

    public $partners = [];

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
            'inputs.partner_id.required' => 'Customer is required',
            'inputs.label.required' => 'Label is required',
            'inputs.address.required' => 'Address is required',
        ];
    }

    #[On('cmw.partners.customer-addresses.edit.open')]
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
        $this->inputs['is_active'] = $this->partnerAddress->is_active;

        $this->resetValidation();
        $this->loadPartners();

        $this->modal('edit-customer-address')->show();
    }

    public function loadPartners()
    {
        $this->partners = Partner::where('is_active', true)
            ->where('is_customer', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($partner) => [
                'value' => $partner->id,
                'label' => "{$partner->name} ({$partner->code})",
            ])
            ->toArray();
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

        Flux::toast('Customer address updated successfully', variant: 'success', position: 'top-end');

        $this->dispatch('cmw.partners.customer-addresses.refresh');
        $this->modal('edit-customer-address')->close();
    }

    public function render()
    {
        return view('livewire.partners.customer-addresses.edit');
    }
}
