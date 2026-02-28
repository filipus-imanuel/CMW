<?php

namespace App\Livewire\Sales\Order\Index;

use App\Models\CMW\Transaction\OrderHeader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class CancelledDataTable extends DataTableComponent
{
    protected $model = OrderHeader::class;

    protected $listeners = [
        'shp.sales.order.refresh.cancelled' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setDefaultSort('updated_at', 'desc');
        $this->setSearchStatus(true);
        $this->setColumnSelectStatus(true);
        $this->setPerPageAccepted([10, 25, 50, 100]);
        $this->setPerPage(25);
    }

    public function builder(): Builder
    {
        return OrderHeader::query()
            ->cancelled()
            ->with(['partner', 'itemCategory', 'createdBy']);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'enable_this_row' => ! $row->trashed(),
                    'showDetail' => Auth::user()?->can('view sales order'),
                    'detailHref' => route('sales.order.show', ['id' => $row->id]),
                ])),

            Column::make('Code', 'code_request')
                ->sortable()
                ->searchable(),

            Column::make('Date', 'date')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y')),

            Column::make('Customer', 'partner_id')
                ->sortable()
                ->searchable(fn ($query, $term) => $query->orWhereHas('partner', fn ($q) => $q->where('name', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->partner ? "{$row->partner->name} ({$row->partner->code})" : 'N/A'),

            Column::make('Category', 'item_category_id')
                ->sortable()
                ->format(fn ($value, $row) => $row->itemCategory?->name ?? 'N/A'),

            Column::make('Total', 'total')
                ->sortable()
                ->format(fn ($value) => number_format((float) $value, 2)),

            Column::make('Cancellation Reason', 'rejection_reason')
                ->format(fn ($value) => str($value)->limit(60)),

            Column::make('Requested By', 'created_by')
                ->format(fn ($value, $row) => $row->createdBy?->name ?? 'N/A'),
        ];
    }
}
