<?php

namespace App\Livewire\Masters\CategoryPrice;

use App\Models\CMW\Master\CategoryPrice;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Category Price')]
class Create extends Component
{
    public $inputs = [
        'code' => '',
        'name' => '',
        'remarks' => '',
        'is_active' => true,
    ];

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:category_prices,code',
            'inputs.name' => 'required|string|max:100|unique:category_prices,name',
            'inputs.remarks' => 'nullable|string|max:1024',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function store(): void
    {
        $this->authorize('create category price');

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                CategoryPrice::create([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'created_by' => Auth::id(),
                ]);

                Flux::toast('Category Price created successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.category-price.refresh');
                $this->modal('create-category-price')->close();
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('Category Price with this name or code already exists', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while creating category price', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.category-price.create.open')]
    public function openModal(): void
    {
        $this->authorize('create category price');

        $this->inputs = [
            'code' => '',
            'name' => '',
            'remarks' => '',
            'is_active' => true,
        ];
        $this->resetValidation();
        $this->modal('create-category-price')->show();
    }

    public function render()
    {
        return view('livewire.masters.category-price.create');
    }
}
