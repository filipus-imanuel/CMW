<?php

namespace App\Livewire\Sales\Request;

use App\Helpers\CMW\PopulateDataHelper;
use App\Helpers\CMW\PriceResolutionHelper;
use App\Helpers\CMW\TransactionHelper;
use App\Models\CMW\History\HistoryItemPrice;
use App\Models\CMW\Inventory\CategoryPrice;
use App\Models\CMW\Inventory\ItemPrice;
use App\Models\CMW\Inventory\ItemUom;
use App\Models\CMW\Inventory\PendingItemPrice;
use App\Models\CMW\Master\Partner;
use App\Models\CMW\System\Setting;
use App\Models\CMW\Transaction\OrderDetail;
use App\Models\CMW\Transaction\OrderHeader;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Search & Add Items')]
class Search extends Component
{
    use WithPagination;

    public int $orderId;

    public ?OrderHeader $order = null;

    public string $search = '';

    public string $typeFilter = '';

    public string $uomFilter = '';

    public array $addedUomIds = [];

    public array $dropdown_uom = [];

    // Price edit modal state
    public ?ItemPrice $editingItemPrice = null;

    public $priceInputs = [
        'price' => 0,
        'remarks' => '',
    ];

    /**
     * Get the approval threshold from system settings.
     */
    #[Computed]
    public function threshold(): float
    {
        return (float) Setting::get('inventory.item_price.threshold_bypass_approval', 0);
    }

    public function mount(int $id): void
    {
        $this->authorize('edit sales request');

        $this->order = OrderHeader::with(['partner', 'company', 'itemCategory', 'currency'])
            ->findOrFail($id);

        if ($this->order->status !== 'INIT') {
            $this->redirectRoute('sales.request.index.init', navigate: true);

            return;
        }

        $this->orderId = $id;

        // Track which item_uom_ids are already in this order
        $this->addedUomIds = OrderDetail::where('order_header_id', $id)
            ->pluck('item_uom_id')
            ->toArray();

        $this->dropdown_uom = PopulateDataHelper::getUoms();
    }

    /**
     * Paginated item list with price resolution.
     */
    #[Computed]
    public function items()
    {
        $query = ItemUom::query()
            ->with(['item', 'uom'])
            ->whereHas('item', function ($q) {
                $q->where('is_active', true);

                if ($this->order?->item_category_id) {
                    $q->where('item_category_id', $this->order->item_category_id);
                }

                if ($this->search !== '') {
                    $q->where(function ($sq) {
                        $sq->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('code', 'like', '%'.$this->search.'%');
                    });
                }

                if ($this->typeFilter !== '') {
                    $q->where('type', $this->typeFilter);
                }
            });

        if ($this->uomFilter !== '') {
            $query->where('uom_id', $this->uomFilter);
        }

        $query->orderBy('item_id')
            ->orderByDesc('is_base');

        return $query->paginate(100);
    }

    /**
     * Resolve prices for the current page of items.
     */
    #[Computed]
    public function resolvedPrices(): array
    {
        $items = $this->items;
        $partner = $this->order?->partner;
        $prices = [];

        $categoryPriceIds = [];

        foreach ($items as $itemUom) {
            $item = $itemUom->item;
            if (! $item) {
                continue;
            }

            $resolved = PriceResolutionHelper::resolve($item, $partner, $itemUom->id);
            $hetResolved = PriceResolutionHelper::resolve($item, null, $itemUom->id);

            if ($resolved['category_price_id']) {
                $categoryPriceIds[] = $resolved['category_price_id'];
            }

            $prices[$itemUom->id] = [
                'het_price' => $hetResolved['price'],
                'sell_price' => $resolved['price'],
                'price_source' => $resolved['source'],
                'category_price_id' => $resolved['category_price_id'],
                'het_category_price_id' => $hetResolved['category_price_id'],
                'category_price_code' => null,
            ];
        }

        // Batch-load category price codes
        $categoryPriceCodes = CategoryPrice::whereIn('id', array_unique(array_filter($categoryPriceIds)))
            ->pluck('code', 'id')
            ->toArray();

        foreach ($prices as $uomId => &$price) {
            $price['category_price_code'] = $price['category_price_id']
                ? ($categoryPriceCodes[$price['category_price_id']] ?? null)
                : null;
        }

        return $prices;
    }

    /**
     * Reset filters and pagination.
     */
    public function resetFilters(): void
    {
        $this->search = '';
        $this->typeFilter = '';
        $this->uomFilter = '';
        $this->resetPage();
    }

    /**
     * Reset pagination when filters change.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedUomFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Add an item to the order directly in the database.
     */
    public function addItem(int $itemUomId): void
    {
        $this->authorize('edit sales request');

        // Duplicate check
        if (in_array($itemUomId, $this->addedUomIds)) {
            Flux::toast('Item already added to the order', variant: 'warning', position: 'top-end');

            return;
        }

        $itemUom = ItemUom::with(['item', 'uom'])->findOrFail($itemUomId);
        $item = $itemUom->item;

        if (! $item) {
            Flux::toast('Item not found', variant: 'danger', position: 'top-end');

            return;
        }

        $partner = $this->order->partner;
        $resolved = PriceResolutionHelper::resolve($item, $partner, $itemUomId);

        // Block add when the resolved price has a pending approval
        if ($resolved['category_price_id']) {
            $itemPrice = ItemPrice::where('item_uom_id', $itemUomId)
                ->where('category_price_id', $resolved['category_price_id'])
                ->first();

            if ($itemPrice) {
                $hasPending = PendingItemPrice::where('item_price_id', $itemPrice->id)
                    ->where('status', 'pending')
                    ->exists();

                if ($hasPending) {
                    Flux::toast(
                        heading: 'Add Blocked',
                        text: 'This item has a price pending approval. Please resolve the approval first before adding it to the order.',
                        variant: 'warning',
                        position: 'top-end',
                    );

                    return;
                }
            }
        }

        $sellPrice = $resolved['price'];

        // Calculate tax for this item
        $taxMode = $this->order->tax_mode ?? 'NONE';
        $taxRate = (float) ($this->order->tax_rate ?? 0);
        $calc = TransactionHelper::calculateItemTax(1, $sellPrice, 0, $taxMode, $taxRate);

        DB::transaction(function () use ($itemUom, $sellPrice, $calc) {
            OrderDetail::create([
                'order_header_id' => $this->order->id,
                'item_id' => $itemUom->item_id,
                'item_uom_id' => $itemUom->id,
                'quantity' => 1,
                'price_proposed' => $sellPrice,
                'price_deal' => $sellPrice,
                'discount' => 0,
                'tax' => $calc['tax'],
                'total' => $calc['total'],
                'created_by' => Auth::id(),
            ]);

            TransactionHelper::updateOrderTotals($this->order);
        });

        $this->addedUomIds[] = $itemUomId;

        Flux::toast("Added: {$itemUom->item->name} ({$itemUom->uom->name})", variant: 'success', position: 'top-end');
    }

    /**
     * Open the edit price modal for a specific item price.
     */
    public function editPrice(int $itemUomId): void
    {
        $this->authorize('edit item price');
        $this->resetValidation();

        $partner = $this->order->partner;
        $categoryPriceId = $partner?->category_price_id;

        // Fallback to GEN category if partner has none
        if (! $categoryPriceId) {
            $genCategory = CategoryPrice::where('code', 'GEN')->first();
            $categoryPriceId = $genCategory?->id;
        }

        if (! $categoryPriceId) {
            Flux::toast('No pricing category found for this customer', variant: 'danger', position: 'top-end');

            return;
        }

        // Find or build the item price record
        $this->editingItemPrice = ItemPrice::with(['itemUom.item', 'itemUom.uom', 'categoryPrice'])
            ->where('item_uom_id', $itemUomId)
            ->where('category_price_id', $categoryPriceId)
            ->first();

        if (! $this->editingItemPrice) {
            Flux::toast('No price record found for this item/category combination. Create it in Inventory > Item Prices first.', variant: 'warning', position: 'top-end');

            return;
        }

        // Block editing if there's a pending approval
        $existingPending = PendingItemPrice::where('item_price_id', $this->editingItemPrice->id)
            ->where('status', 'pending')
            ->first();

        if ($existingPending) {
            Flux::toast(
                heading: 'Edit Blocked',
                text: 'This price has a pending approval. Please approve or reject it first before making new changes.',
                variant: 'warning',
                position: 'top-end',
            );

            return;
        }

        $this->priceInputs = [
            'price' => $this->editingItemPrice->price,
            'remarks' => $this->editingItemPrice->remarks ?? '',
        ];

        $this->modal('edit-price')->show();
    }

    /**
     * Update the master item price with threshold/approval logic.
     */
    public function updatePrice(): void
    {
        $this->authorize('edit item price');

        try {
            $validated = $this->validate([
                'priceInputs.price' => 'required|numeric|min:0',
                'priceInputs.remarks' => 'nullable|string|max:1024',
            ]);

            $oldPrice = (float) $this->editingItemPrice->price;
            $newPrice = (float) $validated['priceInputs']['price'];

            if ($oldPrice === $newPrice) {
                // Only update remarks if price hasn't changed
                $this->editingItemPrice->update([
                    'remarks' => $validated['priceInputs']['remarks'],
                    'updated_by' => Auth::id(),
                ]);

                Flux::toast('Item Price remarks updated', variant: 'success', position: 'top-end');
                $this->modal('edit-price')->close();
                $this->editingItemPrice = null;

                return;
            }

            // Calculate change percentage
            if ($oldPrice <= 0) {
                $changePercentage = 100.00;
            } else {
                $changePercentage = (($newPrice - $oldPrice) / $oldPrice) * 100;
            }

            $threshold = $this->threshold;

            // Check if change exceeds threshold
            if ($threshold > 0 && abs($changePercentage) > $threshold) {
                DB::transaction(function () use ($validated, $oldPrice, $newPrice, $changePercentage) {
                    PendingItemPrice::create([
                        'item_price_id' => $this->editingItemPrice->id,
                        'item_uom_id' => $this->editingItemPrice->item_uom_id,
                        'category_price_id' => $this->editingItemPrice->category_price_id,
                        'old_price' => $oldPrice,
                        'new_price' => $newPrice,
                        'change_percentage' => $changePercentage,
                        'status' => 'pending',
                        'submitted_by' => Auth::id(),
                        'submitted_at' => now(),
                        'created_by' => Auth::id(),
                    ]);

                    $this->editingItemPrice->update([
                        'remarks' => $validated['priceInputs']['remarks'],
                        'updated_by' => Auth::id(),
                    ]);
                });

                Flux::toast(
                    heading: 'Pending Approval',
                    text: 'Price change submitted for approval. It will take effect after manager review.',
                    variant: 'warning',
                    position: 'top-end',
                );
                $this->dispatch('item-price-approval.badge-refresh');
                $this->modal('edit-price')->close();
                $this->editingItemPrice = null;

                return;
            }

            // Direct update (below threshold or threshold disabled)
            DB::transaction(function () use ($validated, $oldPrice, $newPrice) {
                HistoryItemPrice::create([
                    'item_uom_id' => $this->editingItemPrice->item_uom_id,
                    'category_price_id' => $this->editingItemPrice->category_price_id,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'created_by' => Auth::id(),
                ]);

                $this->editingItemPrice->update([
                    'price' => $newPrice,
                    'remarks' => $validated['priceInputs']['remarks'],
                    'updated_by' => Auth::id(),
                ]);
            });

            Flux::toast('Item Price updated successfully', variant: 'success', position: 'top-end');
            $this->modal('edit-price')->close();
            $this->editingItemPrice = null;
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top-end');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('Database error while updating price', variant: 'danger', position: 'top-end');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while updating item price', variant: 'danger', position: 'top-end');
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.sales.request.search');
    }
}
