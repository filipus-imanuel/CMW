<?php

namespace App\Livewire\Inventories\Item;

use App\Models\CMW\Master\Item;
use App\Models\CMW\Master\Uom;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Item')]
class Edit extends Component
{
    public ?Item $item = null;

    public $inputs = [];

    public $uoms = [];

    public function rules()
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:items,code,'.$this->item?->id,
            'inputs.name' => 'required|string|max:100',
            'inputs.type' => 'required|in:RAW_MATERIAL,WORK_IN_PROCESS,FINISHED_GOOD,SPARE_PART',
            'inputs.uom_id' => 'required|exists:uoms,id',
            'inputs.cost_price' => 'required|numeric|min:0',
            'inputs.sell_price' => 'required|numeric|min:0',
            'inputs.min_stock' => 'nullable|numeric|min:0',
            'inputs.max_stock' => 'nullable|numeric|min:0',
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
            'inputs.type.required' => 'Type is required',
            'inputs.uom_id.required' => 'UOM is required',
            'inputs.cost_price.required' => 'Cost price is required',
            'inputs.sell_price.required' => 'Sell price is required',
        ];
    }

    #[On('cmw.inventories.item.edit.open')]
    public function openModal($id)
    {
        $this->authorize('edit item');

        $this->item = Item::findOrFail($id);

        $this->inputs['code'] = $this->item->code;
        $this->inputs['name'] = $this->item->name;
        $this->inputs['type'] = $this->item->type;
        $this->inputs['uom_id'] = $this->item->uom_id;
        $this->inputs['cost_price'] = $this->item->cost_price;
        $this->inputs['sell_price'] = $this->item->sell_price;
        $this->inputs['min_stock'] = $this->item->min_stock;
        $this->inputs['max_stock'] = $this->item->max_stock;
        $this->inputs['remarks'] = $this->item->remarks;
        $this->inputs['is_active'] = $this->item->is_active;

        $this->resetValidation();
        $this->loadUoms();

        $this->modal('edit-item')->show();
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
        $this->authorize('edit item');

        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            $this->item->update([
                ...$validated['inputs'],
                'updated_by' => Auth::id(),
            ]);
        });

        Flux::toast('Item updated successfully', variant: 'success', position: 'top-end');

        $this->dispatch('cmw.inventories.item.refresh');
        $this->modal('edit-item')->close();
    }

    public function render()
    {
        return view('livewire.inventories.item.edit');
    }
}
