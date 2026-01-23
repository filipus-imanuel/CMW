<?php

namespace App\Livewire\Masters\Currency;

use App\Models\CMW\Master\Currency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class IndexDataTable extends DataTableComponent
{
    protected $model = Currency::class;

    protected $listeners = [
        'cmw.master.currency.refresh' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('code', 'asc')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search currencies...')
            ->setColumnSelectEnabled()
            ->setEmptyMessage('No data found');
    }

    public function builder(): Builder
    {
        return Currency::query()
            ->with(['createdBy', 'updatedBy']);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'showEdit' => Auth::user()?->can('edit currency'),
                    'editDispatchEvent' => 'cmw.master.currency.edit.open',
                    'showDelete' => Auth::user()?->can('delete currency') && ! $row->is_delete_locked,
                    'deleteDispatchEvent' => 'delete',
                ])),

            Column::make('Code', 'code')
                ->sortable()
                ->searchable(),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Symbol', 'symbol')
                ->sortable()
                ->searchable(),

            Column::make('Symbol Position', 'symbol_position')
                ->sortable()
                ->format(fn ($value) => $value === 'BEFORE' ? 'Before Amount' : 'After Amount'),

            Column::make('Rate', 'rate')
                ->sortable()
                ->format(fn ($value) => number_format($value, 5)),

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
