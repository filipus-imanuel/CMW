<?php

namespace App\Livewire\Masters\ItemCategory;

use App\Helpers\CMW\PopulateDataHelper;
use App\Models\CMW\Master\ItemCategory;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Item Category')]
class Edit extends Component
{
    public ?ItemCategory $itemCategory = null;

    public $inputs = [
        'code' => '',
        'name' => '',
        'companies' => [],
        'remarks' => '',
        'is_active' => true,
    ];

    public $dropdown_data = [];

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:item_categories,code,'.$this->itemCategory?->id,
            'inputs.name' => 'required|string|max:100|unique:item_categories,name,'.$this->itemCategory?->id,
            'inputs.companies' => 'required|array|min:1',
            'inputs.companies.*' => 'exists:companies,id',
            'inputs.remarks' => 'nullable|string|max:1024',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'inputs.companies.required' => 'Please select at least one company.',
            'inputs.companies.min' => 'Please select at least one company.',
        ];
    }

    public function update(): void
    {
        $this->authorize('edit item category');

        try {
            if ($this->itemCategory->is_edit_locked) {
                Flux::toast('This item category cannot be edited.', variant: 'danger', position: 'top right');

                return;
            }

            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                $this->itemCategory->update([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'updated_by' => Auth::id(),
                ]);

                // Sync companies - this will add/remove as needed
                $this->itemCategory->companies()->sync($validated['inputs']['companies']);

                Flux::toast('Item category updated successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.item-category.refresh');
                $this->modal('edit-item-category')->close();
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('Item category with this name or code already exists', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while updating item category', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.item-category.edit.open')]
    public function openModal($id): void
    {
        $this->authorize('edit item category');
        $this->resetValidation();

        $this->itemCategory = ItemCategory::with('companies')->findOrFail($id);

        $this->inputs = [
            'code' => $this->itemCategory->code,
            'name' => $this->itemCategory->name,
            'companies' => $this->itemCategory->companies->pluck('id')->toArray(),
            'remarks' => $this->itemCategory->remarks,
            'is_active' => $this->itemCategory->is_active,
        ];

        $this->loadDropdownData();
        $this->modal('edit-item-category')->show();
    }

    protected function loadDropdownData(): void
    {
        $this->dropdown_data['companies'] = PopulateDataHelper::getCompanies();
    }

    public function render()
    {
        return view('livewire.masters.item-category.edit');
    }
}
