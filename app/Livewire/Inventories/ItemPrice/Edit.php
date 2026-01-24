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

#[Title('Edit Item Price')]
class Edit extends Component
{
    public ?ItemPrice $itemPrice = null;

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

    public function update(): void
    {
        $this->authorize('edit item price');

        try {
            $validated = $this->validate();

            // Check for duplicate item+category combination (excluding current record)
            $exists = ItemPrice::where('item_id', $validated['inputs']['item_id'])
                ->where('category_price_id', $validated['inputs']['category_price_id'])
                ->where('id', '!=', $this->itemPrice->id)
                ->exists();

            if ($exists) {
                Flux::toast('This item already has a price for this category', variant: 'danger', position: 'top right');

                return;
            }

            DB::transaction(function () use ($validated) {
                $oldPrice = $this->itemPrice->price;
                $newPrice = $validated['inputs']['price'];

                // Log history if price changed
                if ((float) $oldPrice !== (float) $newPrice) {
                    HistoryItemPrice::create([
                        'item_id' => $this->itemPrice->item_id,
                        'category_price_id' => $this->itemPrice->category_price_id,
                        'old_price' => $oldPrice,
                        'new_price' => $newPrice,
                        'created_by' => Auth::id(),
                    ]);
                }

                $this->itemPrice->update([
                    'item_id' => $validated['inputs']['item_id'],
                    'category_price_id' => $validated['inputs']['category_price_id'],
                    'price' => $newPrice,
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'updated_by' => Auth::id(),
                ]);

                Flux::toast('Item Price updated successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.inventories.item-price.refresh');
                $this->modal('edit-item-price')->close();
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('This item already has a price for this category', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while updating item price', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.inventories.item-price.edit.open')]
    public function openModal($id): void
    {
        $this->authorize('edit item price');
        $this->resetValidation();

        $this->itemPrice = ItemPrice::with(['item', 'categoryPrice'])->findOrFail($id);

        $this->inputs = [
            'item_id' => $this->itemPrice->item_id,
            'category_price_id' => $this->itemPrice->category_price_id,
            'price' => $this->itemPrice->price,
            'remarks' => $this->itemPrice->remarks,
            'is_active' => $this->itemPrice->is_active,
        ];

        $this->loadDropdowns();
        $this->modal('edit-item-price')->show();
    }

    public function render()
    {
        return view('livewire.inventories.item-price.edit');
    }
}
