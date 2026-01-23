<?php

namespace App\Livewire\Masters\Country;

use App\Models\CMW\Master\Country;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Country')]
class Edit extends Component
{
    public ?Country $country = null;

    public $inputs = [
        'code' => '',
        'name' => '',
        'remarks' => '',
        'is_active' => true,
    ];

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:countries,code,'.$this->country?->id,
            'inputs.name' => 'required|string|max:255|unique:countries,name,'.$this->country?->id,
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function update(): void
    {
        $this->authorize('edit country');

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                $this->country->update([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'updated_by' => Auth::id(),
                ]);

                Flux::toast('Country updated successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.country.refresh');
                $this->modal('edit-country')->close();
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('Country with this name or code already exists', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while updating country', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.country.edit.open')]
    public function openModal($id): void
    {
        $this->authorize('edit country');
        $this->resetValidation();

        $this->country = Country::findOrFail($id);

        $this->inputs = [
            'code' => $this->country->code,
            'name' => $this->country->name,
            'remarks' => $this->country->remarks,
            'is_active' => $this->country->is_active,
        ];

        $this->modal('edit-country')->show();
    }

    public function render()
    {
        return view('livewire.masters.country.edit');
    }
}
