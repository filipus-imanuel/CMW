<?php

namespace App\Livewire\Inventories\Adjustment;

use App\Helpers\CMW\CodeGeneratorHelper;
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

#[Title('Create Stock Adjustment')]
class Create extends Component
{
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

    public function mount(): void
    {
        $this->authorize('create stock adjustment');

        $this->inputs = [
            'date' => now()->format('Y-m-d'),
            'warehouse_id' => '',
            'remarks' => '',
        ];

        $this->lines = [];

        $this->loadDropdowns();
    }

    private function loadDropdowns(): void
    {
        $this->dropdown_warehouses = PopulateDataHelper::getWarehouses(['useCache' => false]);
        $this->dropdown_items = PopulateDataHelper::getItems(['useCache' => false]);
    }

    public function addLine(): void
    {
        $this->lines[] = [
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
     * When item_id changes on a line, fetch the system quantity from InventoryLedger.
     */
    public function updatedLines($value, string $key): void
    {
        // Parse the key to get index and field
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

    /**
     * Fetch the current system balance for an item in the selected warehouse,
     * converted to the selected UOM.
     */
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

    /**
     * Recalculate quantity_difference = quantity_actual - quantity_system.
     */
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

    public function store(): void
    {
        $this->authorize('create stock adjustment');

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                $header = StockAdjustmentHeader::create([
                    'code' => CodeGeneratorHelper::generateAdjustmentCode(),
                    'date' => $validated['inputs']['date'],
                    'warehouse_id' => $validated['inputs']['warehouse_id'],
                    'status' => StockAdjustmentHeader::STATUS_DRAFT,
                    'remarks' => $validated['inputs']['remarks'] ?? null,
                    'created_by' => Auth::id(),
                ]);

                foreach ($validated['lines'] as $line) {
                    $quantitySystem = (float) ($line['quantity_system'] ?? 0);
                    $quantityActual = (float) $line['quantity_actual'];
                    $quantityDifference = $quantityActual - $quantitySystem;

                    StockAdjustmentDetail::create([
                        'stock_adjustment_header_id' => $header->id,
                        'item_id' => $line['item_id'],
                        'item_uom_id' => $line['item_uom_id'],
                        'warehouse_id' => $validated['inputs']['warehouse_id'],
                        'quantity_system' => $quantitySystem,
                        'quantity_actual' => $quantityActual,
                        'quantity_difference' => $quantityDifference,
                        'remarks' => $line['remarks'] ?? null,
                        'created_by' => Auth::id(),
                    ]);
                }

                Flux::toast('Stock adjustment created successfully', variant: 'success', position: 'top right');
                $this->redirectRoute('inventories.stock-adjustments.show', ['id' => $header->id], navigate: true);
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('Database error occurred while creating stock adjustment', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while creating stock adjustment', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.inventories.adjustment.create');
    }
}
