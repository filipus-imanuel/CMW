<?php

namespace App\Livewire\Masters\UomConversion;

use App\Helpers\CMW\PopulateDataHelper;
use App\Models\CMW\Master\UomConversion;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('UOM Conversion')]
class Create extends Component
{
    public $inputs = [];

    public $dropdown_from_uom = [];

    public $dropdown_to_uom = [];

    public function rules()
    {
        return [
            'inputs.from_uom_id' => 'required|exists:uoms,id|different:inputs.to_uom_id',
            'inputs.to_uom_id' => 'required|exists:uoms,id',
            'inputs.conversion_rate' => 'required|numeric|min:0.000001',
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'inputs.from_uom_id.required' => 'From UOM is required',
            'inputs.from_uom_id.different' => 'From UOM and To UOM must be different',
            'inputs.to_uom_id.required' => 'To UOM is required',
            'inputs.conversion_rate.required' => 'Conversion rate is required',
            'inputs.conversion_rate.numeric' => 'Conversion rate must be a number',
            'inputs.conversion_rate.min' => 'Conversion rate must be greater than 0',
        ];
    }

    private function handlePopulateUom(): void
    {
        // Both dropdowns share the same data
        $uomData = PopulateDataHelper::getUoms(['labelFormat' => 'name_code']);
        $this->dropdown_from_uom = $uomData;
        $this->dropdown_to_uom = $uomData;

        // Set default values to first item (index 0 since no prependDefault)
        $this->inputs['from_uom_id'] = $uomData[0]['value'] ?? null;
        $this->inputs['to_uom_id'] = $uomData[1]['value'] ?? $uomData[0]['value'] ?? null;
    }

    #[On('cmw.master.uom-conversion.create.open')]
    public function openModal()
    {
        $this->authorize('create uom conversion');

        $this->reset(['inputs']);
        $this->inputs['is_active'] = true;
        $this->resetValidation();

        $this->handlePopulateUom();

        $this->modal('create-uom-conversion')->show();
    }

    public function save()
    {
        $this->authorize('create uom conversion');

        $validated = $this->validate();

        // Check if conversion already exists
        $exists = UomConversion::where('from_uom_id', $validated['inputs']['from_uom_id'])
            ->where('to_uom_id', $validated['inputs']['to_uom_id'])
            ->exists();

        if ($exists) {
            $this->addError('inputs.from_uom_id', 'This conversion already exists');

            return;
        }

        DB::transaction(function () use ($validated) {
            UomConversion::create([
                ...$validated['inputs'],
                'created_by' => Auth::id(),
            ]);
        });

        Flux::toast('UOM conversion created successfully', variant: 'success', position: 'top-end');

        $this->dispatch('cmw.master.uom-conversion.refresh');
        $this->modal('create-uom-conversion')->close();
    }

    public function render()
    {
        return view('livewire.masters.uom-conversion.create');
    }
}
