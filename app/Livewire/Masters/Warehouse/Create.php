<?php

namespace App\Livewire\Masters\Warehouse;

use App\Models\CMW\Master\Warehouse;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Warehouse')]
class Create extends Component
{
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
            'inputs.code' => 'required|string|max:50|unique:warehouses,code',
            'inputs.name' => 'required|string|max:255|unique:warehouses,name',
            'inputs.address' => 'nullable|string|max:500',
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function store(): void
    {
        $this->authorize('create warehouse');

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                Warehouse::create([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'address' => $validated['inputs']['address'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'created_by' => Auth::id(),
                ]);

                Flux::toast('Warehouse created successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.warehouse.refresh');
                $this->modal('create-warehouse')->close();
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('Warehouse with this name or code already exists', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while creating warehouse', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.warehouse.create.open')]
    public function openModal(): void
    {
        $this->authorize('create warehouse');

        $this->inputs = [
            'code' => '',
            'name' => '',
            'address' => '',
            'remarks' => '',
            'is_active' => true,
        ];
        $this->resetValidation();
        $this->modal('create-warehouse')->show();
    }

    public function render()
    {
        return view('livewire.masters.warehouse.create');
    }
}
