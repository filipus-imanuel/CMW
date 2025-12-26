<?php

namespace App\Livewire\Masters\CreditTerm;

use App\Helpers\CMW\PopulateDataHelper;
use App\Models\CMW\Master\CreditTerm;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Credit Term')]
class Edit extends Component
{
    public ?CreditTerm $creditTerm = null;

    public $inputs = [];

    public $dropdown_partner_address = [];

    public function rules()
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:credit_terms,code,'.$this->creditTerm?->id,
            'inputs.name' => 'required|string|max:100',
            'inputs.partner_address_id' => 'nullable|exists:partner_addresses,id',
            'inputs.days' => 'required|integer|min:0|max:365',
            'inputs.description' => 'nullable|string|max:500',
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'inputs.code.required' => 'Code is required',
            'inputs.code.unique' => 'This code already exists',
            'inputs.name.required' => 'Name is required',
            'inputs.days.required' => 'Days is required',
            'inputs.days.integer' => 'Days must be a number',
            'inputs.days.min' => 'Days must be at least 0',
            'inputs.days.max' => 'Days cannot exceed 365',
        ];
    }

    private function handlePopulatePartnerAddress(): void
    {
        $this->dropdown_partner_address = PopulateDataHelper::getPartnerAddresses();
    }

    #[On('cmw.master.credit-term.edit.open')]
    public function openModal($id)
    {
        $this->authorize('edit credit term');

        $this->creditTerm = CreditTerm::findOrFail($id);

        $this->inputs['code'] = $this->creditTerm->code;
        $this->inputs['name'] = $this->creditTerm->name;
        $this->inputs['partner_address_id'] = $this->creditTerm->partner_address_id;
        $this->inputs['days'] = $this->creditTerm->days;
        $this->inputs['description'] = $this->creditTerm->description;
        $this->inputs['remarks'] = $this->creditTerm->remarks;
        $this->inputs['is_active'] = (bool) $this->creditTerm->is_active;

        $this->resetValidation();
        $this->handlePopulatePartnerAddress();

        $this->modal('edit-credit-term')->show();
    }

    public function save()
    {
        $this->authorize('edit credit term');

        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            $this->creditTerm->update([
                ...$validated['inputs'],
                'updated_by' => Auth::id(),
            ]);
        });

        Flux::toast('Credit term updated successfully', variant: 'success', position: 'top-end');

        $this->dispatch('cmw.master.credit-term.refresh');
        $this->modal('edit-credit-term')->close();
    }

    public function render()
    {
        return view('livewire.masters.credit-term.edit');
    }
}
