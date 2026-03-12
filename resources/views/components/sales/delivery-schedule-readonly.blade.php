@props(['order'])

@php
    $hasSchedules = $order->relationLoaded('deliverySchedules')
        ? $order->deliverySchedules->isNotEmpty()
        : $order->deliverySchedules()->exists();

    if ($hasSchedules) {
        $schedulesByDetail = $order->relationLoaded('deliverySchedules')
            ? $order->deliverySchedules->groupBy('order_detail_id')
            : $order->deliverySchedules()->get()->groupBy('order_detail_id');
    }
@endphp

@if($hasSchedules)
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Delivery Schedule</flux:heading>

        @foreach($order->details as $detailIndex => $detail)
            @php
                $detailSchedules = $schedulesByDetail[$detail->id] ?? collect();
            @endphp

            @if($detailSchedules->isNotEmpty())
                <div class="{{ $detailIndex > 0 ? 'mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-700' : '' }}">
                    <flux:subheading class="mb-2">
                        #{{ $detailIndex + 1 }} — {{ $detail->item?->code }} · {{ $detail->item?->name }}
                        <span class="text-zinc-400">({{ $detail->itemUom?->uom?->name }} · Qty: {{ number_format((float)$detail->quantity, 2) }})</span>
                    </flux:subheading>

                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column class="w-12">#</flux:table.column>
                            <flux:table.column>Delivery Date</flux:table.column>
                            <flux:table.column class="text-center">Quantity</flux:table.column>
                            <flux:table.column class="text-center">%</flux:table.column>
                            <flux:table.column>Remarks</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($detailSchedules as $si => $schedule)
                                @php
                                    $detailQty = (float)$detail->quantity;
                                    $schedQty = (float)$schedule->quantity;
                                    $pct = $detailQty > 0 ? round(($schedQty / $detailQty) * 100, 2) : 0;
                                @endphp
                                <flux:table.row>
                                    <flux:table.cell>{{ $si + 1 }}</flux:table.cell>
                                    <flux:table.cell>{{ $schedule->delivery_date?->format('d M Y') }}</flux:table.cell>
                                    <flux:table.cell class="text-right tabular-nums">{{ number_format($schedQty, 2) }}</flux:table.cell>
                                    <flux:table.cell class="text-right tabular-nums">{{ number_format($pct, 2) }}%</flux:table.cell>
                                    <flux:table.cell>{{ $schedule->remarks ?? '-' }}</flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            @endif
        @endforeach
    </flux:card>
@endif
