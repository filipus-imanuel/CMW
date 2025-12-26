<?php

namespace App\Livewire\Masters\Employee;

use App\Models\CMW\Master\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class IndexDataTable extends DataTableComponent
{
    protected $model = Employee::class;

    protected $listeners = [
        'cmw.master.employee.refresh' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setDefaultSort('code', 'asc');
        $this->setSearchStatus(true);
        $this->setColumnSelectStatus(true);
        $this->setPerPageAccepted([10, 25, 50, 100]);
        $this->setPerPage(25);
    }

    public function builder(): Builder
    {
        return Employee::query()
            ->with(['position']);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'showEdit' => Auth::user()?->can('edit employee'),
                    'editDispatchEvent' => 'cmw.master.employee.edit.open',
                    'showDelete' => Auth::user()?->can('delete employee'),
                    'deleteDispatchEvent' => 'cmw.master.employee.delete',
                ])),

            Column::make('Code', 'code')
                ->sortable()
                ->searchable(),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Position', 'position.name')
                ->sortable()
                ->searchable(),

            Column::make('Email', 'email')
                ->sortable()
                ->searchable()
                ->format(fn ($value) => $value ?: '-'),

            Column::make('Phone', 'phone')
                ->sortable()
                ->searchable()
                ->format(fn ($value) => $value ?: '-'),

            BooleanColumn::make('Status', 'is_active')
                ->sortable(),
        ];
    }
}
