<?php

namespace App\Livewire\Sales\Return\Index;

use App\Models\CMW\Transaction\ReturnHeader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class DraftDataTable extends DataTableComponent
{
    protected $model = ReturnHeader::class;

    protected $listeners = [
        'sales.return.refresh.draft' => '$refresh',
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
            ->draft()
            ->with(['partner', 'deliveryHeader', 'createdBy']);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'enable_this_row' => ! $row->trashed(),
                    'showEdit' => Auth::user()?->can('edit sales return'),
                    'editHref' => route('sales.return.edit', ['id' => $row->id]),
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
                ->format(fn ($value) => '<span class="px-2 py-1 text-xs font-medium rounded '.($value === 'ITEM' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200').'">'.$value.'</span>'),

            Column::make('Total', 'total')
                ->sortable()
                ->format(fn ($value) => number_format((float) $value, 2)),

            Column::make('Created By', 'created_by')
                ->format(fn ($value, $row) => $row->createdBy?->name ?? 'N/A'),
        ];
    }
}
