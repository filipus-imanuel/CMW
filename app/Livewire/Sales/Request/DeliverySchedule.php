<?php

namespace App\Livewire\Sales\Request;

use App\Models\CMW\Transaction\OrderDeliverySchedule;
use App\Models\CMW\Transaction\OrderHeader;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Delivery Schedule')]
class DeliverySchedule extends Component
{
    public ?OrderHeader $order = null;

    /**
     * Schedule rows keyed by detail index.
     * Each entry: array of [delivery_date, quantity, percentage, remarks]
     *
     * @var array<int, array<int, array{delivery_date: string, quantity: string, percentage: string, remarks: string}>>
     */
    public $schedules = [];

    /**
     * Input mode per detail index: 'qty' or 'pct'.
     *
     * @var array<int, string>
     */
    public $inputMode = [];

    /**
     * Detail metadata for display (read-only).
     *
     * @var array<int, array{id: int, item_code: string, item_name: string, uom_name: string, quantity: string}>
     */
    public $detailMeta = [];

    public function mount($id): void
    {
        $this->authorize('edit sales request');

        $this->order = OrderHeader::with([
            'partner', 'company', 'itemCategory',
            'details.item', 'details.itemUom.uom', 'details.deliverySchedules',
        ])->findOrFail($id);

        if ($this->order->status !== 'INIT') {
            $this->redirectRoute('sales.request.index.init', navigate: true);

            return;
        }

        if ($this->order->details->isEmpty()) {
            Flux::toast('Please add at least one item before configuring delivery schedule.', variant: 'warning', position: 'top-end');
            $this->redirectRoute('sales.request.edit', ['id' => $this->order->id], navigate: true);

            return;
        }

        $this->populateSchedules();
    }

    private function populateSchedules(): void
    {
        $headerDate = $this->order->delivery_date?->format('Y-m-d') ?? '';

        foreach ($this->order->details as $index => $detail) {
            $isReturn = ! empty($detail->return_detail_id);
            $this->detailMeta[$index] = [
                'id' => $detail->id,
                'item_code' => $detail->item?->code ?? '',
                'item_name' => ($isReturn ? '↩ ' : '').($detail->item?->name ?? ''),
                'uom_name' => $detail->itemUom?->uom?->name ?? '',
                'quantity' => number_format((float) $detail->quantity, 2, '.', ''),
            ];

            $this->inputMode[$index] = 'qty';

            $existing = $detail->deliverySchedules;
            if ($existing->isNotEmpty()) {
                $detailQty = (float) $detail->quantity;
                $this->schedules[$index] = $existing->map(function ($schedule) use ($detailQty) {
                    $qty = (float) $schedule->quantity;
                    $pct = $detailQty > 0 ? round(($qty / $detailQty) * 100, 2) : 0;

                    return [
                        'delivery_date' => $schedule->delivery_date?->format('Y-m-d') ?? '',
                        'quantity' => number_format($qty, 2, '.', ''),
                        'percentage' => number_format($pct, 2, '.', ''),
                        'remarks' => $schedule->remarks ?? '',
                    ];
                })->toArray();
            } else {
                // Pre-fill: 1 row with full quantity and header delivery_date
                $qty = (float) $detail->quantity;
                $this->schedules[$index] = [
                    [
                        'delivery_date' => $headerDate,
                        'quantity' => number_format($qty, 2, '.', ''),
                        'percentage' => '100.00',
                        'remarks' => '',
                    ],
                ];
            }
        }
    }

    public function addRow(int $detailIndex): void
    {
        if (! isset($this->schedules[$detailIndex])) {
            return;
        }

        $this->schedules[$detailIndex][] = [
            'delivery_date' => '',
            'quantity' => '0.00',
            'percentage' => '0.00',
            'remarks' => '',
        ];
    }

    public function removeRow(int $detailIndex, int $rowIndex): void
    {
        if (! isset($this->schedules[$detailIndex][$rowIndex])) {
            return;
        }

        // Must keep at least 1 row
        if (count($this->schedules[$detailIndex]) <= 1) {
            Flux::toast('Each item must have at least one schedule row.', variant: 'warning', position: 'top-end');

            return;
        }

        unset($this->schedules[$detailIndex][$rowIndex]);
        $this->schedules[$detailIndex] = array_values($this->schedules[$detailIndex]);
    }

    public function toggleInputMode(int $detailIndex): void
    {
        if (! isset($this->inputMode[$detailIndex])) {
            return;
        }

        $this->inputMode[$detailIndex] = $this->inputMode[$detailIndex] === 'qty' ? 'pct' : 'qty';
    }

    /**
     * When schedule fields are updated, auto-calculate the counterpart (qty ↔ pct).
     */
    public function updatedSchedules($value, $key): void
    {
        // $key format: "0.1.quantity", "0.1.percentage", "0.0.delivery_date" etc.
        $parts = explode('.', $key);
        if (count($parts) !== 3) {
            return;
        }

        $detailIndex = (int) $parts[0];
        $rowIndex = (int) $parts[1];
        $field = $parts[2];

        if (! isset($this->detailMeta[$detailIndex]) || ! isset($this->schedules[$detailIndex][$rowIndex])) {
            return;
        }

        $detailQty = (float) ($this->detailMeta[$detailIndex]['quantity'] ?? 0);
        if ($detailQty <= 0) {
            return;
        }

        $mode = $this->inputMode[$detailIndex] ?? 'qty';

        if ($field === 'quantity' && $mode === 'qty') {
            $qty = (float) ($value ?? 0);
            $pct = round(($qty / $detailQty) * 100, 2);
            $this->schedules[$detailIndex][$rowIndex]['percentage'] = number_format($pct, 2, '.', '');
        } elseif ($field === 'percentage' && $mode === 'pct') {
            $pct = (float) ($value ?? 0);
            $qty = round(($pct / 100) * $detailQty, 2);
            $this->schedules[$detailIndex][$rowIndex]['quantity'] = number_format($qty, 2, '.', '');
        }
    }

    public function save(): void
    {
        $this->authorize('edit sales request');

        // Validate
        $errors = [];
        foreach ($this->schedules as $detailIndex => $rows) {
            $detailQty = (float) ($this->detailMeta[$detailIndex]['quantity'] ?? 0);
            $sumQty = 0;

            foreach ($rows as $rowIndex => $row) {
                if (empty($row['delivery_date'])) {
                    $errors[] = 'Item #'.($detailIndex + 1).', row #'.($rowIndex + 1).': delivery date is required.';
                }
                $qty = (float) ($row['quantity'] ?? 0);
                if ($qty <= 0) {
                    $errors[] = 'Item #'.($detailIndex + 1).', row #'.($rowIndex + 1).': quantity must be greater than 0.';
                }
                $sumQty += $qty;
            }

            $diff = abs($sumQty - $detailQty);
            if ($diff > 0.01) {
                $errors[] = 'Item #'.($detailIndex + 1).': total scheduled quantity ('.number_format($sumQty, 2).') does not match order quantity ('.number_format($detailQty, 2).').';
            }
        }

        if (! empty($errors)) {
            foreach ($errors as $error) {
                Flux::toast($error, variant: 'danger', position: 'top-end', duration: 6000);
            }

            return;
        }

        DB::transaction(function () {
            $earliestDate = null;

            // Delete existing schedules for this order
            OrderDeliverySchedule::where('order_header_id', $this->order->id)
                ->each(function ($schedule) {
                    $schedule->update(['deleted_by' => Auth::id()]);
                    $schedule->delete();
                });

            // Create new schedules
            foreach ($this->schedules as $detailIndex => $rows) {
                $detailId = $this->detailMeta[$detailIndex]['id'];

                foreach ($rows as $row) {
                    OrderDeliverySchedule::create([
                        'order_header_id' => $this->order->id,
                        'order_detail_id' => $detailId,
                        'delivery_date' => $row['delivery_date'],
                        'quantity' => (float) $row['quantity'],
                        'remarks' => $row['remarks'] ?: null,
                        'created_by' => Auth::id(),
                    ]);

                    $date = $row['delivery_date'];
                    if ($date && ($earliestDate === null || $date < $earliestDate)) {
                        $earliestDate = $date;
                    }
                }
            }

            // Update header delivery_date to earliest schedule date
            if ($earliestDate) {
                $this->order->update([
                    'delivery_date' => $earliestDate,
                    'updated_by' => Auth::id(),
                ]);
            }
        });

        Flux::toast('Delivery schedule saved successfully.', variant: 'success', position: 'top-end');
        $this->dispatch('sales.request.refresh.init');
        $this->redirectRoute('sales.request.edit', ['id' => $this->order->id], navigate: true);
    }

    /**
     * Get the remaining/excess quantity for a specific detail item.
     */
    public function getBalance(int $detailIndex): array
    {
        $detailQty = (float) ($this->detailMeta[$detailIndex]['quantity'] ?? 0);
        $sumQty = 0;

        foreach ($this->schedules[$detailIndex] ?? [] as $row) {
            $sumQty += (float) ($row['quantity'] ?? 0);
        }

        $diff = round($detailQty - $sumQty, 2);

        return [
            'total' => $detailQty,
            'scheduled' => round($sumQty, 2),
            'remaining' => $diff,
            'balanced' => abs($diff) <= 0.01,
        ];
    }

    public function render()
    {
        return view('livewire.sales.request.delivery-schedule');
    }
}
