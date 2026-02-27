<?php

namespace App\Livewire\Warehouses\Delivery\Finish;

use App\Models\CMW\Transaction\DeliveryHeader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class IndexDataTable extends DataTableComponent
{
    protected $model = DeliveryHeader::class;

    protected $listeners = [
        'shp.warehouse.delivery.refresh.finish' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setDefaultSort('confirmed_at', 'desc');
        $this->setSearchStatus(true);
        $this->setColumnSelectStatus(true);
        $this->setPerPageAccepted([10, 25, 50, 100]);
        $this->setPerPage(25);
    }

    public function builder(): Builder
    {
        return DeliveryHeader::query()
            ->finished()
            ->with(['orderHeader', 'partner', 'company', 'confirmedByUser']);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'enable_this_row' => ! $row->trashed(),
                    'showDetail' => Auth::user()?->can('view delivery order'),
                    'detailHref' => route('warehouses.delivery.finish.show', ['id' => $row->id]),
                ])),

            Column::make('DO Code', 'code')
                ->sortable()
                ->searchable(),

            Column::make('SO Code', 'order_header_id')
                ->sortable()
                ->searchable(fn ($query, $term) => $query->orWhereHas('orderHeader', fn ($q) => $q->where('code_order', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->orderHeader?->code_order ?? '-'),

            Column::make('Customer', 'partner_id')
                ->sortable()
                ->searchable(fn ($query, $term) => $query->orWhereHas('partner', fn ($q) => $q->where('name', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->partner ? "{$row->partner->name} ({$row->partner->code})" : 'N/A'),

            Column::make('Date', 'date')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y')),

            Column::make('Confirmed At', 'confirmed_at')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y H:i') ?? '-'),

            Column::make('Confirmed By', 'confirmed_by')
                ->format(fn ($value, $row) => $row->confirmedByUser?->name ?? '-'),

            Column::make('Total', 'total')
                ->sortable()
                ->format(fn ($value) => number_format((float) $value, 2)),
        ];
    }
}
