<?php

namespace App\Livewire\Sales\Return\Index;

use App\Models\CMW\Transaction\ReturnHeader;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class RejectedDataTable extends DataTableComponent
{
    protected $model = ReturnHeader::class;

    protected $listeners = [
        'shp.sales.return.refresh.rejected' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setDefaultSort('created_at', 'desc');
        $this->setSearchStatus(true);
        $this->setColumnSelectStatus(true);
        $this->setPerPageAccepted([10, 25, 50, 100]);
        $this->setPerPage(25);
    }

    public function builder(): Builder
    {
        return ReturnHeader::query()
            ->salesOrder()
            ->rejected()
            ->with(['partner', 'deliveryHeader', 'createdBy']);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'enable_this_row' => true,
                    'showDetail' => true,
                    'detailHref' => route('sales.return.show', ['id' => $row->id]),
                ])),

            Column::make('Code', 'code')
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
                ->format(fn ($value, $row) => $row->deliveryHeader?->code ?? 'N/A'),

            Column::make('Type', 'return_type')
                ->sortable()
                ->html()
                ->format(fn ($value) => ReturnHeader::returnTypeHtmlBadge($value)),

            Column::make('Rejection Reason', 'rejection_reason')
                ->format(fn ($value) => $value ? \Illuminate\Support\Str::limit($value, 50) : '-'),

            Column::make('Created By', 'created_by')
                ->format(fn ($value, $row) => $row->createdBy?->name ?? 'N/A'),
        ];
    }
}
