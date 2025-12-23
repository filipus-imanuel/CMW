<?php

namespace App\Livewire\Masters\Tax;

use App\Models\CMW\Master\Tax;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Tax')]
class Create extends Component
{
    public $inputs = [
        'code' => '',
        'name' => '',
        'rate' => 0,
        'remarks' => '',
        'is_active' => true,
    ];

    public function mount(): void
    {
        $this->authorize('create tax');
    }

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:taxes,code',
            'inputs.name' => 'required|string|max:255|unique:taxes,name',
            'inputs.rate' => 'required|numeric|min:0|max:100',
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->authorize('create tax');
        
        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                Tax::create([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'rate' => $validated['inputs']['rate'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'created_by' => Auth::id(),
                ]);

                Flux::toast('Tax created successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.tax.refresh');
                $this->modal('create-tax')->close();
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                Flux::toast('Tax with this name or code already exists', variant: 'danger', position: 'top right');
            } else {
                Flux::toast('Database error: ' . $e->getMessage(), variant: 'danger', position: 'top right');
            }
            throw $e;
        } catch (\Exception $e) {
            Flux::toast('Failed to create tax: ' . $e->getMessage(), variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.tax.create.open')]
    public function openModal(): void
    {
        $this->inputs = [
            'code' => '',
            'name' => '',
            'rate' => 0,
            'remarks' => '',
            'is_active' => true,
        ];
        $this->resetValidation();
        $this->modal('create-tax')->show();
    }

    public function render()
    {
        return view('livewire.masters.tax.create');
    }
}
