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

#[Title('Item')]
class Create extends Component
{
    public $inputs = [];

    public $uoms = [];

    public function rules()
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:items,code',
            'inputs.name' => 'required|string|max:100',
            'inputs.type' => 'required|in:RAW_MATERIAL,WORK_IN_PROCESS,FINISHED_GOOD,SPARE_PART',
            'inputs.uom_id' => 'required|exists:uoms,id',
            'inputs.cost_price' => 'required|numeric|min:0',
            'inputs.sell_price' => 'required|numeric|min:0',
            'inputs.min_stock' => 'nullable|numeric|min:0',
            'inputs.max_stock' => 'nullable|numeric|min:0',
            'inputs.remarks' => 'nullable|string|max:500',
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

    #[On('cmw.inventories.item.create.open')]
    public function openModal()
    {
        $this->authorize('create item');

        $this->reset(['inputs']);
        $this->inputs['type'] = 'FINISHED_GOOD';
        $this->inputs['cost_price'] = 0;
        $this->inputs['sell_price'] = 0;
        $this->inputs['min_stock'] = 0;
        $this->inputs['max_stock'] = 0;

        $this->resetValidation();
        $this->loadUoms();

        $this->modal('create-item')->show();
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
        $this->authorize('create item');

        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            Item::create([
                ...$validated['inputs'],
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);
        });

        Flux::toast('Item created successfully', variant: 'success', position: 'top-end');

        $this->dispatch('cmw.inventories.item.refresh');
        $this->modal('create-item')->close();
    }

    public function render()
    {
        return view('livewire.inventories.item.create');
    }
}
