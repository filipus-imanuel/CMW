<?php

namespace App\Livewire\Employees\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class IndexDataTable extends DataTableComponent
{
    protected $model = User::class;

    protected $listeners = [
        'cmw.employee.user.refresh' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setDefaultSort('name', 'asc');
        $this->setSearchStatus(true);
        $this->setColumnSelectStatus(true);
        $this->setPerPageAccepted([10, 25, 50, 100]);
        $this->setPerPage(25);
    }

    public function builder(): Builder
    {
        return User::query()
            ->where('users.id', '>', 1)
            ->with(['department']);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'showEdit' => Auth::user()?->can('edit user'),
                    'editHref' => route('employees.users.edit', $row->id),
                    'showDelete' => Auth::user()?->can('delete user'),
                    'deleteDispatchEvent' => 'cmw.employee.user.delete',
                ])),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Email', 'email')
                ->sortable()
                ->searchable(),

            Column::make('Department', 'department.name')
                ->sortable()
                ->searchable()
                ->format(fn ($value) => $value ?: '-'),

            Column::make('Phone', 'phone')
                ->sortable()
                ->searchable()
                ->format(fn ($value) => $value ?: '-'),

            BooleanColumn::make('Active', 'is_active')
                ->sortable(),

            Column::make('Created At', 'created_at')
                ->sortable()
                ->deselected(),

            Column::make('Updated At', 'updated_at')
                ->sortable()
                ->deselected(),
        ];
    }
}
