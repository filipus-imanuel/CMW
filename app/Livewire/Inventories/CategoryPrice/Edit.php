<?php

namespace App\Livewire\Inventories\CategoryPrice;

use App\Models\CMW\Inventory\CategoryPrice;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Category Price')]
class Edit extends Component
{
    public ?CategoryPrice $categoryPrice = null;

    public $inputs = [
        'code' => '',
        'name' => '',
        'remarks' => '',
        'is_active' => true,
    ];

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:category_prices,code,'.$this->categoryPrice?->id,
            'inputs.name' => 'required|string|max:100|unique:category_prices,name,'.$this->categoryPrice?->id,
            'inputs.remarks' => 'nullable|string|max:1024',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function update(): void
    {
        $this->authorize('edit category price');

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                $this->categoryPrice->update([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'updated_by' => Auth::id(),
                ]);

                Flux::toast('Category Price updated successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.inventory.category-price.refresh');
                $this->modal('edit-category-price')->close();
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('Category Price with this name or code already exists', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while updating category price', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.inventory.category-price.edit.open')]
    public function openModal($id): void
    {
        $this->authorize('edit category price');
        $this->resetValidation();

        $this->categoryPrice = CategoryPrice::findOrFail($id);

        $this->inputs = [
            'code' => $this->categoryPrice->code,
            'name' => $this->categoryPrice->name,
            'remarks' => $this->categoryPrice->remarks,
            'is_active' => $this->categoryPrice->is_active,
        ];

        $this->modal('edit-category-price')->show();
    }

    public function render()
    {
        return view('livewire.inventories.category-price.edit');
    }
}
