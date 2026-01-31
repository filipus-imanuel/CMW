<?php

namespace App\Livewire\Inventories\ItemPrice;

use App\Models\CMW\History\HistoryItemPrice;
use App\Models\CMW\Inventory\ItemPrice;
use App\Models\CMW\Inventory\PendingItemPrice;
use App\Models\CMW\System\Setting;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Item Price')]
class Edit extends Component
{
    public ?ItemPrice $itemPrice = null;

    public $inputs = [
        'price' => 0,
        'remarks' => '',
        'is_active' => true,
    ];

    /**
     * Get the approval threshold from system settings.
     */
    #[Computed]
    public function threshold(): float
    {
        return Setting::get('inventory.item_price.threshold_bypass_approval', 0);
    }

    public function rules(): array
    {
        return [
            'inputs.price' => 'required|numeric|min:0',
            'inputs.remarks' => 'nullable|string|max:1024',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function update(): void
    {
        $this->authorize('edit item price');

        try {
            $validated = $this->validate();

            $oldPrice = (float) $this->itemPrice->price;
            $newPrice = (float) $validated['inputs']['price'];

            // Calculate change percentage with guard for division by zero
            if ($oldPrice <= 0) {
                $changePercentage = 100.00;
            } else {
                $changePercentage = (($newPrice - $oldPrice) / $oldPrice) * 100;
            }

            $threshold = $this->threshold;

            // Check if change exceeds threshold and threshold is enabled
            if ($threshold > 0 && abs($changePercentage) > $threshold && (float) $oldPrice !== $newPrice) {
                // Submit for approval instead of direct update
                DB::transaction(function () use ($validated, $oldPrice, $newPrice, $changePercentage) {
                    PendingItemPrice::create([
                        'item_price_id' => $this->itemPrice->id,
                        'item_id' => $this->itemPrice->item_id,
                        'category_price_id' => $this->itemPrice->category_price_id,
                        'old_price' => $oldPrice,
                        'new_price' => $newPrice,
                        'change_percentage' => $changePercentage,
                        'status' => 'pending',
                        'submitted_by' => Auth::id(),
                        'submitted_at' => now(),
                        'created_by' => Auth::id(),
                    ]);

                    // Update non-price fields if needed
                    $this->itemPrice->update([
                        'remarks' => $validated['inputs']['remarks'],
                        'is_active' => $validated['inputs']['is_active'],
                        'updated_by' => Auth::id(),
                    ]);
                });

                Flux::toast('Price change submitted for approval', variant: 'info', position: 'top right');
                $this->dispatch('item-price-approval.badge-refresh');
                $this->dispatch('cmw.inventories.item-price.refresh');
                $this->modal('edit-item-price')->close();

                return;
            }

            // Direct update (below threshold or threshold disabled)
            DB::transaction(function () use ($validated, $oldPrice, $newPrice) {
                // Log history if price changed
                if ($oldPrice !== $newPrice) {
                    HistoryItemPrice::create([
                        'item_id' => $this->itemPrice->item_id,
                        'category_price_id' => $this->itemPrice->category_price_id,
                        'old_price' => $oldPrice,
                        'new_price' => $newPrice,
                        'created_by' => Auth::id(),
                    ]);
                }

                $this->itemPrice->update([
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

        // Auto-reject any existing pending approval for this item price
        $existingPending = PendingItemPrice::where('item_price_id', $id)
            ->where('status', 'pending')
            ->first();

        if ($existingPending) {
            DB::transaction(function () use ($existingPending) {
                $existingPending->update([
                    'status' => 'rejected',
                    'approved_by' => Auth::id(),
                    'reviewed_at' => now(),
                    'processed_at' => now(),
                    'approval_notes' => 'Auto-rejected: New price change submitted',
                    'updated_by' => Auth::id(),
                ]);
            });

            Flux::toast('Previous pending approval was automatically rejected', variant: 'warning', position: 'top right');
            $this->dispatch('item-price-approval.badge-refresh');
        }

        $this->inputs = [
            'price' => $this->itemPrice->price,
            'remarks' => $this->itemPrice->remarks,
            'is_active' => $this->itemPrice->is_active,
        ];

        $this->modal('edit-item-price')->show();
    }

    public function render()
    {
        return view('livewire.inventories.item-price.edit');
    }
}
