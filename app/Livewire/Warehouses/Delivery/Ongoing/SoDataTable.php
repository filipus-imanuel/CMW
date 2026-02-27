<?php

namespace App\Livewire\Warehouses\Delivery\Ongoing;

use App\Models\CMW\Transaction\OrderHeader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class SoDataTable extends DataTableComponent
{
    protected $model = OrderHeader::class;

    protected $listeners = [
        'shp.warehouse.delivery.refresh.ongoing-so' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setDefaultSort('delivery_date', 'asc');
        $this->setSearchStatus(true);
        $this->setColumnSelectStatus(true);
        $this->setPerPageAccepted([10, 25, 50, 100]);
        $this->setPerPage(25);
    }

    public function builder(): Builder
    {
        return OrderHeader::query()
            ->where('status', 'DELIVERY')
            ->with(['partner', 'company', 'deliveries']);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'enable_this_row' => ! $row->trashed(),
                    'showDetail' => Auth::user()?->can('view delivery order'),
                    'detailHref' => route('warehouses.delivery.create', ['orderId' => $row->id]),
                ])),

            Column::make('SO Code', 'code_order')
                ->sortable()
                ->searchable(),

            Column::make('Customer', 'partner_id')
                ->sortable()
                ->searchable(fn ($query, $term) => $query->orWhereHas('partner', fn ($q) => $q->where('name', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->partner ? "{$row->partner->name} ({$row->partner->code})" : 'N/A'),

            Column::make('Company', 'company_id')
                ->format(fn ($value, $row) => $row->company?->name ?? '-'),

            Column::make('Delivery Date', 'delivery_date')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y') ?? '-'),

            Column::make('Total', 'total')
                ->sortable()
                ->format(fn ($value) => number_format((float) $value, 2)),

            Column::make('Deliveries', 'id')
                ->label(fn ($row) => $row->deliveries->count().' DO(s)')
                ->html(false),
        ];
    }
}
