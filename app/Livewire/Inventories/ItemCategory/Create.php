<?php

namespace App\Livewire\Inventories\ItemCategory;

use App\Helpers\CMW\PopulateDataHelper;
use App\Models\CMW\Inventory\ItemCategory;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Item Category')]
class Create extends Component
{
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
            'inputs.code' => 'required|string|max:50|unique:item_categories,code',
            'inputs.name' => 'required|string|max:100|unique:item_categories,name',
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

    public function store(): void
    {
        $this->authorize('create item category');

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                $itemCategory = ItemCategory::create([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'created_by' => Auth::id(),
                ]);

                // Attach companies to the item category
                $itemCategory->companies()->attach($validated['inputs']['companies']);

                Flux::toast('Item category created successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.inventory.item-category.refresh');
                $this->modal('create-item-category')->close();
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('Item category with this name or code already exists', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while creating item category', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.inventory.item-category.create.open')]
    public function openModal(): void
    {
        $this->authorize('create item category');

        $this->inputs = [
            'code' => '',
            'name' => '',
            'companies' => [],
            'remarks' => '',
            'is_active' => true,
        ];
        $this->resetValidation();
        $this->loadDropdownData();
        $this->modal('create-item-category')->show();
    }

    protected function loadDropdownData(): void
    {
        $this->dropdown_data['companies'] = PopulateDataHelper::getCompanies();
    }

    public function render()
    {
        return view('livewire.inventories.item-category.create');
    }
}
