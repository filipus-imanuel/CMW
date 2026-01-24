<?php

namespace App\Livewire\Inventories\ItemPrice;

use App\Helpers\CMW\PopulateDataHelper;
use App\Models\CMW\History\HistoryItemPrice;
use App\Models\CMW\Master\ItemPrice;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Item Price')]
class Create extends Component
{
    public $inputs = [
        'item_id' => '',
        'category_price_id' => '',
        'price' => 0,
        'remarks' => '',
        'is_active' => true,
    ];

    public $dropdown_items = [];

    public $dropdown_category_prices = [];

    public function rules(): array
    {
        return [
            'inputs.item_id' => 'required|exists:items,id',
            'inputs.category_price_id' => 'required|exists:category_prices,id',
            'inputs.price' => 'required|numeric|min:0',
            'inputs.remarks' => 'nullable|string|max:1024',
            'inputs.is_active' => 'boolean',
        ];
    }

    private function loadDropdowns(): void
    {
        $this->dropdown_items = PopulateDataHelper::getItems(['labelFormat' => 'code_name']);
        $this->dropdown_category_prices = PopulateDataHelper::getCategoryPrices(['labelFormat' => 'code_name']);
    }

    public function store(): void
    {
        $this->authorize('create item price');

        try {
            $validated = $this->validate();

            // Check for duplicate item+category combination
            $exists = ItemPrice::where('item_id', $validated['inputs']['item_id'])
                ->where('category_price_id', $validated['inputs']['category_price_id'])
                ->exists();

            if ($exists) {
                Flux::toast('This item already has a price for this category', variant: 'danger', position: 'top right');

                return;
            }

            DB::transaction(function () use ($validated) {
                $itemPrice = ItemPrice::create([
                    'item_id' => $validated['inputs']['item_id'],
                    'category_price_id' => $validated['inputs']['category_price_id'],
                    'price' => $validated['inputs']['price'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'created_by' => Auth::id(),
                ]);

                // Log history with old_price = 0 for new records
                HistoryItemPrice::create([
                    'item_id' => $itemPrice->item_id,
                    'category_price_id' => $itemPrice->category_price_id,
                    'old_price' => 0,
                    'new_price' => $itemPrice->price,
                    'created_by' => Auth::id(),
                ]);

                Flux::toast('Item Price created successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.inventories.item-price.refresh');
                $this->modal('create-item-price')->close();
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('This item already has a price for this category', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while creating item price', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.inventories.item-price.create.open')]
    public function openModal(): void
    {
        $this->authorize('create item price');

        $this->inputs = [
            'item_id' => '',
            'category_price_id' => '',
            'price' => 0,
            'remarks' => '',
            'is_active' => true,
        ];
        $this->resetValidation();
        $this->loadDropdowns();
        $this->modal('create-item-price')->show();
    }

    public function render()
    {
        return view('livewire.inventories.item-price.create');
    }
}
