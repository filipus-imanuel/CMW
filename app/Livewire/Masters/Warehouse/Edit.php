<?php

namespace App\Livewire\Masters\Warehouse;

use App\Models\CMW\Master\Warehouse;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Warehouse')]
class Edit extends Component
{
    public ?Warehouse $warehouse = null;

    public $inputs = [
        'code' => '',
        'name' => '',
        'address' => '',
        'remarks' => '',
        'is_active' => true,
    ];

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:warehouses,code,'.$this->warehouse?->id,
            'inputs.name' => 'required|string|max:255|unique:warehouses,name,'.$this->warehouse?->id,
            'inputs.address' => 'nullable|string|max:500',
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function update(): void
    {
        $this->authorize('edit warehouse');

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                $this->warehouse->update([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'address' => $validated['inputs']['address'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'updated_by' => Auth::id(),
                ]);

                Flux::toast('Warehouse updated successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.warehouse.refresh');
                $this->modal('edit-warehouse')->close();
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                Flux::toast('Warehouse with this name or code already exists', variant: 'danger', position: 'top right');
            } else {
                Flux::toast('Database error: '.$e->getMessage(), variant: 'danger', position: 'top right');
            }
            throw $e;
        } catch (\Exception $e) {
            Flux::toast('Failed to update warehouse: '.$e->getMessage(), variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.warehouse.edit.open')]
    public function openModal($id): void
    {
        $this->authorize('edit warehouse');
        $this->resetValidation();

        $this->warehouse = Warehouse::findOrFail($id);

        $this->inputs = [
            'code' => $this->warehouse->code,
            'name' => $this->warehouse->name,
            'address' => $this->warehouse->address,
            'remarks' => $this->warehouse->remarks,
            'is_active' => $this->warehouse->is_active,
        ];

        $this->modal('edit-warehouse')->show();
    }

    public function render()
    {
        return view('livewire.masters.warehouse.edit');
    }
}
