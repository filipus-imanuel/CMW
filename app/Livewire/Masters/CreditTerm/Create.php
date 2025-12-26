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

#[Title('Credit Term')]
class Create extends Component
{
    public $inputs = [];

    public $dropdown_partner_address = [];

    public function rules()
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:credit_terms,code',
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

        // Set default value to first item (index 0 since no prependDefault)
        $this->inputs['partner_address_id'] = $this->dropdown_partner_address[0]['value'] ?? null;
    }

    #[On('cmw.master.credit-term.create.open')]
    public function openModal()
    {
        $this->authorize('create credit term');

        $this->reset(['inputs']);
        $this->inputs['is_active'] = true;
        $this->resetValidation();

        $this->handlePopulatePartnerAddress();

        $this->modal('create-credit-term')->show();
    }

    public function save()
    {
        $this->authorize('create credit term');

        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            CreditTerm::create([
                ...$validated['inputs'],
                'created_by' => Auth::id(),
            ]);
        });

        Flux::toast('Credit term created successfully', variant: 'success', position: 'top-end');

        $this->dispatch('cmw.master.credit-term.refresh');
        $this->modal('create-credit-term')->close();
    }

    public function render()
    {
        return view('livewire.masters.credit-term.create');
    }
}
