<?php

namespace App\Livewire\Masters\Company;

use App\Models\CMW\Master\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class IndexDataTable extends DataTableComponent
{
    protected $model = Company::class;

    protected $listeners = [
        'cmw.master.company.refresh' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('code', 'asc')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search companies...')
            ->setColumnSelectEnabled()
            ->setEmptyMessage('No data found');
    }

    public function builder(): Builder
    {
        return Company::query()
            ->with(['currency', 'createdBy', 'updatedBy']);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'showEdit' => Auth::user()?->can('edit company'),
                    'editDispatchEvent' => 'cmw.master.company.edit.open',
                    'showDelete' => Auth::user()?->can('delete company') && ! $row->is_delete_locked,
                    'deleteDispatchEvent' => 'delete',
                ])),

            Column::make('Code', 'code')
                ->sortable()
                ->searchable(),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Sales Limit', 'sales_limit')
                ->sortable()
                ->format(fn ($value, $row) => $row->currency ? $row->currency->formatAmount($value) : number_format($value, 5)),

            Column::make('Currency', 'currency_id')
                ->sortable()
                ->searchable(fn ($query, $term) => $query->orWhereHas('currency', fn ($q) => $q->where('code', 'like', "%{$term}%")->orWhere('name', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->currency ? "{$row->currency->code} - {$row->currency->name}" : '-'),

            Column::make('Remarks', 'remarks')
                ->sortable()
                ->searchable(),

            BooleanColumn::make('Edit Locked', 'is_edit_locked')
                ->sortable(),

            BooleanColumn::make('Delete Locked', 'is_delete_locked')
                ->sortable(),

            BooleanColumn::make('Status', 'is_active')
                ->sortable(),

            Column::make('Created At', 'created_at')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y H:i')),
        ];
    }
}
