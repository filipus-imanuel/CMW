<?php

namespace App\Livewire\Inventories\Adjustment;

use App\Helpers\CMW\PopulateDataHelper;
use App\Models\CMW\Inventory\InventoryLedger;
use App\Models\CMW\Inventory\ItemUom;
use App\Models\CMW\Transaction\StockAdjustmentDetail;
use App\Models\CMW\Transaction\StockAdjustmentHeader;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Stock Adjustment')]
class Edit extends Component
{
    public ?StockAdjustmentHeader $header = null;

    public $inputs = [];

    public $lines = [];

    public $dropdown_warehouses = [];

    public $dropdown_items = [];

    public function rules(): array
    {
        return [
            'inputs.date' => 'required|date',
            'inputs.warehouse_id' => 'required|exists:warehouses,id',
            'inputs.remarks' => 'nullable|string|max:1024',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.item_uom_id' => 'required|exists:item_uoms,id',
            'lines.*.quantity_actual' => 'required|numeric|min:0',
            'lines.*.remarks' => 'nullable|string|max:1024',
        ];
    }

    public function messages(): array
    {
        return [
            'lines.required' => 'At least one line item is required.',
            'lines.min' => 'At least one line item is required.',
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'inputs.date' => 'date',
            'inputs.warehouse_id' => 'warehouse',
            'lines' => 'line items',
            'lines.*.item_id' => 'item',
            'lines.*.item_uom_id' => 'UOM',
            'lines.*.quantity_actual' => 'actual quantity',
        ];
    }

    public function mount($id): void
    {
        $this->authorize('edit stock adjustment');

        $this->header = StockAdjustmentHeader::with(['details.item', 'details.itemUom'])
            ->findOrFail($id);

        // Status guard - only draft can be edited
        if (! $this->header->isDraft()) {
            $this->redirectRoute('inventories.stock-adjustments.show', ['id' => $id], navigate: true);

            return;
        }

        if ($this->header->is_edit_locked) {
            Flux::toast('This stock adjustment is locked and cannot be edited.', variant: 'danger', position: 'top right');
            $this->redirectRoute('inventories.stock-adjustments.show', ['id' => $id], navigate: true);

            return;
        }

        $this->populateInputs();
        $this->loadDropdowns();
    }

    private function populateInputs(): void
    {
        $this->inputs = [
            'date' => $this->header->date->format('Y-m-d'),
            'warehouse_id' => $this->header->warehouse_id,
            'remarks' => $this->header->remarks,
        ];

        $this->lines = $this->header->details->map(function ($detail) {
            return [
                'id' => $detail->id,
                'item_id' => $detail->item_id,
                'item_uom_id' => $detail->item_uom_id,
                'uom_options' => $detail->item_id
                    ? PopulateDataHelper::getItemUomsByItem((int) $detail->item_id)
                    : [],
                'quantity_system' => self::formatQty((float) $detail->quantity_system),
                'quantity_actual' => self::formatQty((float) $detail->quantity_actual),
                'quantity_difference' => self::formatQty((float) $detail->quantity_difference),
                'remarks' => $detail->remarks,
            ];
        })->toArray();
    }

    private function loadDropdowns(): void
    {
        $this->dropdown_warehouses = PopulateDataHelper::getWarehouses(['useCache' => false]);

        // Filter items by selected warehouse
        $warehouseId = $this->inputs['warehouse_id'] ?? null;
        if ($warehouseId) {
            $this->dropdown_items = PopulateDataHelper::getItemsByWarehouse((int) $warehouseId);
        } else {
            $this->dropdown_items = PopulateDataHelper::getItems(['useCache' => false]);
        }
    }

    public function addLine(): void
    {
        $this->lines[] = [
            'id' => null,
            'item_id' => '',
            'item_uom_id' => '',
            'uom_options' => [],
            'quantity_system' => '0.00',
            'quantity_actual' => '0.00',
            'quantity_difference' => '0.00',
            'remarks' => '',
        ];
    }

    public function removeLine(int $index): void
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    /**
     * When item_id or item_uom_id changes on a line, fetch the system quantity.
     */
    public function updatedLines($value, string $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) < 2) {
            return;
        }

        $index = (int) $parts[0];
        $field = $parts[1];

        if ($field === 'item_id') {
            $itemId = (int) ($this->lines[$index]['item_id'] ?? 0);
            $this->lines[$index]['item_uom_id'] = '';
            $this->lines[$index]['uom_options'] = $itemId
                ? PopulateDataHelper::getItemUomsByItem($itemId)
                : [];
            $this->fetchSystemQuantity($index);
        }

        if ($field === 'item_uom_id') {
            $this->fetchSystemQuantity($index);
        }

        if ($field === 'quantity_actual') {
            $this->recalculateDifference($index);
        }
    }

    private function fetchSystemQuantity(int $index): void
    {
        $itemId = $this->lines[$index]['item_id'] ?? null;
        $warehouseId = $this->inputs['warehouse_id'] ?? null;
        $itemUomId = $this->lines[$index]['item_uom_id'] ?? null;

        if (! $itemId || ! $warehouseId) {
            $this->lines[$index]['quantity_system'] = '0';
            $this->recalculateDifference($index);

            return;
        }

        // Get latest balance from inventory ledger (always in base UOM)
        $baseBalance = (float) (InventoryLedger::where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->value('balance') ?? 0);

        // Convert base balance to selected UOM
        if ($itemUomId) {
            $conversionRate = (float) (ItemUom::where('id', $itemUomId)->value('conversion_rate') ?? 1);
            $qty = $conversionRate > 0 ? $baseBalance / $conversionRate : $baseBalance;
        } else {
            $qty = $baseBalance;
        }

        $this->lines[$index]['quantity_system'] = self::formatQty($qty);
        $this->recalculateDifference($index);
    }

    private function recalculateDifference(int $index): void
    {
        $system = (float) ($this->lines[$index]['quantity_system'] ?? 0);
        $actual = (float) ($this->lines[$index]['quantity_actual'] ?? 0);
        $this->lines[$index]['quantity_difference'] = self::formatQty($actual - $system);
    }

    /**
     * Format a quantity value, trimming insignificant trailing zeros.
     */
    private static function formatQty(float $value): string
    {
        return rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.');
    }

    /**
     * When warehouse changes, re-fetch system quantities for all lines
     * and refresh items dropdown to only show items assigned to the selected warehouse.
     */
    public function updatedInputsWarehouseId(): void
    {
        $warehouseId = $this->inputs['warehouse_id'] ?? null;

        // Refresh items dropdown based on selected warehouse
        if ($warehouseId) {
            $this->dropdown_items = PopulateDataHelper::getItemsByWarehouse((int) $warehouseId);
        } else {
            $this->dropdown_items = PopulateDataHelper::getItems(['useCache' => false]);
        }

        foreach ($this->lines as $index => $line) {
            $this->lines[$index]['item_uom_id'] = '';
            $this->lines[$index]['uom_options'] = [];
            if (! empty($line['item_id'])) {
                $this->fetchSystemQuantity($index);
            }
        }
    }

    public function update(): void
    {
        $this->authorize('edit stock adjustment');

        if (! $this->header->isDraft()) {
            Flux::toast('Only draft adjustments can be edited.', variant: 'danger', position: 'top right');

            return;
        }

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                $this->header->update([
                    'date' => $validated['inputs']['date'],
                    'warehouse_id' => $validated['inputs']['warehouse_id'],
                    'remarks' => $validated['inputs']['remarks'] ?? null,
                    'updated_by' => Auth::id(),
                ]);

                // Get existing detail IDs
                $existingDetailIds = $this->header->details()->pluck('id')->toArray();
                $keepDetailIds = [];

                foreach ($validated['lines'] as $index => $line) {
                    $quantitySystem = (float) ($line['quantity_system'] ?? 0);
                    $quantityActual = (float) $line['quantity_actual'];
                    $quantityDifference = $quantityActual - $quantitySystem;

                    $detailId = $this->lines[$index]['id'] ?? null;

                    if ($detailId && in_array($detailId, $existingDetailIds)) {
                        // Update existing detail
                        StockAdjustmentDetail::where('id', $detailId)->update([
                            'item_id' => $line['item_id'],
                            'item_uom_id' => $line['item_uom_id'],
                            'warehouse_id' => $validated['inputs']['warehouse_id'],
                            'quantity_system' => $quantitySystem,
                            'quantity_actual' => $quantityActual,
                            'quantity_difference' => $quantityDifference,
                            'remarks' => $line['remarks'] ?? null,
                            'updated_by' => Auth::id(),
                        ]);
                        $keepDetailIds[] = $detailId;
                    } else {
                        // Create new detail
                        $newDetail = StockAdjustmentDetail::create([
                            'stock_adjustment_header_id' => $this->header->id,
                            'item_id' => $line['item_id'],
                            'item_uom_id' => $line['item_uom_id'],
                            'warehouse_id' => $validated['inputs']['warehouse_id'],
                            'quantity_system' => $quantitySystem,
                            'quantity_actual' => $quantityActual,
                            'quantity_difference' => $quantityDifference,
                            'remarks' => $line['remarks'] ?? null,
                            'created_by' => Auth::id(),
                        ]);
                        $keepDetailIds[] = $newDetail->id;
                    }
                }

                // Soft-delete removed details
                $removeIds = array_diff($existingDetailIds, $keepDetailIds);
                if (! empty($removeIds)) {
                    StockAdjustmentDetail::whereIn('id', $removeIds)->update(['deleted_by' => Auth::id()]);
                    StockAdjustmentDetail::whereIn('id', $removeIds)->delete();
                }

                Flux::toast('Stock adjustment updated successfully', variant: 'success', position: 'top right');
                $this->redirectRoute('inventories.stock-adjustments.show', ['id' => $this->header->id], navigate: true);
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('Database error occurred while updating stock adjustment', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while updating stock adjustment', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.inventories.adjustment.edit');
    }
}
