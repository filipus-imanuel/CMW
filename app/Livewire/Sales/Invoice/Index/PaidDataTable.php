<?php

namespace App\Livewire\Sales\Invoice\Index;

use App\Models\CMW\Transaction\ArInvoiceHeader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class PaidDataTable extends DataTableComponent
{
    protected $model = ArInvoiceHeader::class;

    protected $listeners = [
        'shp.sales.invoice.refresh.paid' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setDefaultSort('date', 'desc');
        $this->setSearchStatus(true);
        $this->setColumnSelectStatus(true);
        $this->setPerPageAccepted([10, 25, 50, 100]);
        $this->setPerPage(25);
    }

    public function builder(): Builder
    {
        return ArInvoiceHeader::query()
            ->paid()
            ->with(['partner', 'orderHeader', 'deliveryHeader', 'currency']);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'enable_this_row' => ! $row->trashed(),
                    'showDetail' => Auth::user()?->can('view ar invoice'),
                    'detailHref' => route('sales.invoice.show', ['id' => $row->id]),
                ])),

            Column::make('Invoice Code', 'code')
                ->sortable()
                ->searchable(),

            Column::make('Date', 'date')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y')),

            Column::make('Customer', 'partner_id')
                ->sortable()
                ->searchable(fn ($query, $term) => $query->orWhereHas('partner', fn ($q) => $q->where('name', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->partner ? "{$row->partner->name} ({$row->partner->code})" : 'N/A'),

            Column::make('DO Code', 'delivery_header_id')
                ->sortable()
                ->searchable(fn ($query, $term) => $query->orWhereHas('deliveryHeader', fn ($q) => $q->where('code', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->deliveryHeader?->code ?? '-'),

            Column::make('SO Code', 'order_header_id')
                ->sortable()
                ->searchable(fn ($query, $term) => $query->orWhereHas('orderHeader', fn ($q) => $q->where('code_order', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->orderHeader?->code_order ?? '-'),

            Column::make('Total', 'total')
                ->sortable()
                ->format(fn ($value) => number_format((float) $value, 2)),

            Column::make('Paid', 'paid')
                ->sortable()
                ->format(fn ($value) => number_format((float) $value, 2)),

            Column::make('Status', 'status')
                ->sortable()
                ->format(fn ($value) => view('components.datatables.invoice-status-badge', ['status' => $value])),
        ];
    }
}
