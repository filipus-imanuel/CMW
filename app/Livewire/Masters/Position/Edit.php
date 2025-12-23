<?php

namespace App\Livewire\Masters\Position;

use App\Models\CMW\Master\Position;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Position')]
class Edit extends Component
{
    public ?Position $position = null;

    public $inputs = [
        'code' => '',
        'name' => '',
        'remarks' => '',
        'is_active' => true,
    ];

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:positions,code,'.$this->position?->id,
            'inputs.name' => 'required|string|max:255|unique:positions,name,'.$this->position?->id,
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function update(): void
    {
        $this->authorize('edit position');
        
        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                $this->position->update([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'updated_by' => Auth::id(),
                ]);

                Flux::toast('Position updated successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.position.refresh');
                $this->modal('edit-position')->close();
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                Flux::toast('Position with this name or code already exists', variant: 'danger', position: 'top right');
            } else {
                Flux::toast('Database error: ' . $e->getMessage(), variant: 'danger', position: 'top right');
            }
            throw $e;
        } catch (\Exception $e) {
            Flux::toast('Failed to update position: ' . $e->getMessage(), variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.position.edit.open')]
    public function openModal($id): void
    {
        $this->authorize('edit position');
        $this->resetValidation();

        $this->position = Position::findOrFail($id);

        $this->inputs = [
            'code' => $this->position->code,
            'name' => $this->position->name,
            'remarks' => $this->position->remarks,
            'is_active' => $this->position->is_active,
        ];

        $this->modal('edit-position')->show();
    }

    public function render()
    {
        return view('livewire.masters.position.edit');
    }
}
