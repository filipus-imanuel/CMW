<?php

namespace App\Livewire\Masters\Uom;

use App\Models\CMW\Master\Uom;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Unit of Measure')]
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
        $this->authorize('create uom');
    }

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:uoms,code',
            'inputs.name' => 'required|string|max:255|unique:uoms,name',
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->authorize('create uom');

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                Uom::create([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'created_by' => Auth::id(),
                ]);

                Flux::toast('Unit of Measure created successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.uom.refresh');
                $this->modal('create-uom')->close();
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
            Flux::toast('Failed to create unit of measure: '.$e->getMessage(), variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.uom.create.open')]
    public function openModal(): void
    {
        $this->inputs = [
            'code' => '',
            'name' => '',
            'remarks' => '',
            'is_active' => true,
        ];
        $this->resetValidation();
        $this->modal('create-uom')->show();
    }

    public function render()
    {
        return view('livewire.masters.uom.create');
    }
}
