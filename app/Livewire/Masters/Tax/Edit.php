<?php

namespace App\Livewire\Masters\Tax;

use App\Models\CMW\Master\Tax;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Tax')]
class Edit extends Component
{
    public ?Tax $tax = null;

    public $inputs = [
        'code' => '',
        'name' => '',
        'rate' => 0,
        'remarks' => '',
        'is_active' => true,
    ];

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:taxes,code,'.$this->tax?->id,
            'inputs.name' => 'required|string|max:255|unique:taxes,name,'.$this->tax?->id,
            'inputs.rate' => 'required|numeric|min:0|max:100',
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function update(): void
    {
        $this->authorize('edit tax');

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                $this->tax->update([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'rate' => $validated['inputs']['rate'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'updated_by' => Auth::id(),
                ]);

                Flux::toast('Tax updated successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.tax.refresh');
                $this->modal('edit-tax')->close();
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                Flux::toast('Tax with this name or code already exists', variant: 'danger', position: 'top right');
            } else {
                Flux::toast('Database error: '.$e->getMessage(), variant: 'danger', position: 'top right');
            }
            throw $e;
        } catch (\Exception $e) {
            Flux::toast('Failed to update tax: '.$e->getMessage(), variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.tax.edit.open')]
    public function openModal($id): void
    {
        $this->authorize('edit tax');
        $this->resetValidation();

        $this->tax = Tax::findOrFail($id);

        $this->inputs = [
            'code' => $this->tax->code,
            'name' => $this->tax->name,
            'rate' => $this->tax->rate,
            'remarks' => $this->tax->remarks,
            'is_active' => (bool) $this->tax->is_active,
        ];

        $this->modal('edit-tax')->show();
    }

    public function render()
    {
        return view('livewire.masters.tax.edit');
    }
}
