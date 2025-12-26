<?php

namespace App\Livewire\Masters\UomConversion;

use App\Models\CMW\Master\Uom;
use App\Models\CMW\Master\UomConversion;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit UOM Conversion')]
class Edit extends Component
{
    public ?UomConversion $conversion = null;

    public $inputs = [];

    public $uoms = [];

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

    #[On('cmw.master.uom-conversion.edit.open')]
    public function openModal($id)
    {
        $this->authorize('edit uom conversion');

        $this->conversion = UomConversion::findOrFail($id);

        $this->inputs['from_uom_id'] = $this->conversion->from_uom_id;
        $this->inputs['to_uom_id'] = $this->conversion->to_uom_id;
        $this->inputs['conversion_rate'] = $this->conversion->conversion_rate;
        $this->inputs['remarks'] = $this->conversion->remarks;
        $this->inputs['is_active'] = $this->conversion->is_active;

        $this->resetValidation();
        $this->loadUoms();

        $this->modal('edit-uom-conversion')->show();
    }

    public function loadUoms()
    {
        $this->uoms = Uom::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($uom) => [
                'value' => $uom->id,
                'label' => "{$uom->name} ({$uom->code})",
            ])
            ->toArray();
    }

    public function save()
    {
        $this->authorize('edit uom conversion');

        $validated = $this->validate();

        // Check if conversion already exists (excluding current record)
        $exists = UomConversion::where('from_uom_id', $validated['inputs']['from_uom_id'])
            ->where('to_uom_id', $validated['inputs']['to_uom_id'])
            ->where('id', '!=', $this->conversion->id)
            ->exists();

        if ($exists) {
            $this->addError('inputs.from_uom_id', 'This conversion already exists');

            return;
        }

        DB::transaction(function () use ($validated) {
            $this->conversion->update([
                ...$validated['inputs'],
                'updated_by' => Auth::id(),
            ]);
        });

        Flux::toast('UOM conversion updated successfully', variant: 'success', position: 'top-end');

        $this->dispatch('cmw.master.uom-conversion.refresh');
        $this->modal('edit-uom-conversion')->close();
    }

    public function render()
    {
        return view('livewire.masters.uom-conversion.edit');
    }
}
