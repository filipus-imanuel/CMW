<?php

namespace App\Livewire\Sales\Payment\Index;

use App\Models\CMW\Transaction\ArPaymentHeader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class CancelledDataTable extends DataTableComponent
{
    protected $model = ArPaymentHeader::class;

    protected $listeners = [
        'shp.sales.payment.refresh.cancelled' => '$refresh',
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
        return ArPaymentHeader::query()
            ->cancelled()
            ->with(['partner', 'company', 'paymentMethod', 'currency']);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'enable_this_row' => ! $row->trashed(),
                    'showDetail' => Auth::user()?->can('view ar payment'),
                    'detailHref' => route('sales.payment.show', ['id' => $row->id]),
                ])),

            Column::make('Payment Code', 'code')
                ->sortable()
                ->searchable(),

            Column::make('Date', 'date')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y')),

            Column::make('Customer', 'partner_id')
                ->sortable()
                ->searchable(fn ($query, $term) => $query->orWhereHas('partner', fn ($q) => $q->where('name', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->partner ? "{$row->partner->name} ({$row->partner->code})" : 'N/A'),

            Column::make('Amount', 'amount')
                ->sortable()
                ->format(fn ($value) => number_format((float) $value, 2)),

            Column::make('Cancel Reason', 'cancel_reason')
                ->sortable()
                ->format(fn ($value) => $value ? \Illuminate\Support\Str::limit($value, 50) : '-'),
        ];
    }
}
