<?php

namespace App\Livewire\Masters\Country;

use App\Models\CMW\Master\Country;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Country')]
class Create extends Component
{
    public $inputs = [
        'code' => '',
        'name' => '',
        'remarks' => '',
        'is_active' => true,
    ];

    public function mount(): void
    {
        $this->authorize('create country');
    }

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:countries,code',
            'inputs.name' => 'required|string|max:255|unique:countries,name',
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->authorize('create country');

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                Country::create([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'created_by' => Auth::id(),
                ]);

                Flux::toast('Country created successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.country.refresh');
                $this->modal('create-country')->close();
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                Flux::toast('Country with this name or code already exists', variant: 'danger', position: 'top right');
            } else {
                Flux::toast('Database error: '.$e->getMessage(), variant: 'danger', position: 'top right');
            }
            throw $e;
        } catch (\Exception $e) {
            Flux::toast('Failed to create country: '.$e->getMessage(), variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.country.create.open')]
    public function openModal(): void
    {
        $this->inputs = [
            'code' => '',
            'name' => '',
            'remarks' => '',
            'is_active' => true,
        ];
        $this->resetValidation();
        $this->modal('create-country')->show();
    }

    public function render()
    {
        return view('livewire.masters.country.create');
    }
}
