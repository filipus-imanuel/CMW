<?php

namespace App\Livewire\Masters\Uom;

use App\Models\CMW\Master\Uom;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Unit of Measure')]
class Edit extends Component
{
    public ?Uom $uom = null;

    public $inputs = [
        'code' => '',
        'name' => '',
        'remarks' => '',
        'is_active' => true,
    ];

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:uoms,code,'.$this->uom?->id,
            'inputs.name' => 'required|string|max:255|unique:uoms,name,'.$this->uom?->id,
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function update(): void
    {
        $this->authorize('edit uom');

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                $this->uom->update([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'updated_by' => Auth::id(),
                ]);

                Flux::toast('Unit of Measure updated successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.uom.refresh');
                $this->modal('edit-uom')->close();
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                Flux::toast('Unit of Measure with this name or code already exists', variant: 'danger', position: 'top right');
            } else {
                Flux::toast('Database error: '.$e->getMessage(), variant: 'danger', position: 'top right');
            }
            throw $e;
        } catch (\Exception $e) {
            Flux::toast('Failed to update unit of measure: '.$e->getMessage(), variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.uom.edit.open')]
    public function openModal($id): void
    {
        $this->authorize('edit uom');
        $this->resetValidation();

        $this->uom = Uom::findOrFail($id);

        $this->inputs = [
            'code' => $this->uom->code,
            'name' => $this->uom->name,
            'remarks' => $this->uom->remarks,
            'is_active' => $this->uom->is_active,
        ];

        $this->modal('edit-uom')->show();
    }

    public function render()
    {
        return view('livewire.masters.uom.edit');
    }
}
