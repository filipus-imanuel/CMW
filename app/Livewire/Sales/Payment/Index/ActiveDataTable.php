<?php

namespace App\Livewire\Sales\Payment\Index;

use App\Models\CMW\Transaction\ArPaymentHeader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class ActiveDataTable extends DataTableComponent
{
    protected $model = ArPaymentHeader::class;

    protected $listeners = [
        'shp.sales.payment.refresh.active' => '$refresh',
        'shp.sales.payment.created' => '$refresh',
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
        return ArPaymentHeader::query()
            ->activePayments()
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

            Column::make('Company', 'company_id')
                ->sortable()
                ->format(fn ($value, $row) => $row->company?->name ?? '-'),

            Column::make('Method', 'payment_method_id')
                ->sortable()
                ->format(fn ($value, $row) => $row->paymentMethod?->name ?? '-'),

            Column::make('Amount', 'amount')
                ->sortable()
                ->format(fn ($value) => number_format((float) $value, 2)),

            Column::make('Reference', 'reference')
                ->sortable()
                ->searchable(),
        ];
    }
}
