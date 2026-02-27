<?php

namespace App\Livewire\Warehouses\Delivery\Upcoming;

use App\Models\CMW\Transaction\OrderHeader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class IndexDataTable extends DataTableComponent
{
    protected $model = OrderHeader::class;

    protected $listeners = [
        'shp.warehouse.delivery.refresh.upcoming' => '$refresh',
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
            ->readyForDelivery()
            ->with(['partner', 'company', 'itemCategory']);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'enable_this_row' => ! $row->trashed(),
                    'showDetail' => Auth::user()?->can('view delivery order'),
                    'detailHref' => route('warehouses.delivery.upcoming.show', ['id' => $row->id]),
                ])),

            Column::make('SO Code', 'code_order')
                ->sortable()
                ->searchable(),

            Column::make('Customer', 'partner_id')
                ->sortable()
                ->searchable(fn ($query, $term) => $query->orWhereHas('partner', fn ($q) => $q->where('name', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->partner ? "{$row->partner->name} ({$row->partner->code})" : 'N/A'),

            Column::make('Company', 'company_id')
                ->sortable()
                ->searchable(fn ($query, $term) => $query->orWhereHas('company', fn ($q) => $q->where('name', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->company?->name ?? '-'),

            Column::make('Category', 'item_category_id')
                ->sortable()
                ->format(fn ($value, $row) => $row->itemCategory?->name ?? '-'),

            Column::make('Delivery Date', 'delivery_date')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y') ?? '-'),

            Column::make('Total', 'total')
                ->sortable()
                ->format(fn ($value) => number_format((float) $value, 2)),

            Column::make('Status', 'status')
                ->sortable()
                ->format(fn ($value) => match ($value) {
                    'ORDER' => '<span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-green-100 text-green-800">Order</span>',
                    'DELIVERY' => '<span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-blue-100 text-blue-800">Delivery</span>',
                    default => '<span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-zinc-100 text-zinc-800">'.$value.'</span>',
                })
                ->html(),
        ];
    }
}
