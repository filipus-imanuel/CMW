<?php

namespace App\Livewire\Employees\Role;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Spatie\Permission\Models\Role;

class IndexDataTable extends DataTableComponent
{
    protected $model = Role::class;

    protected $listeners = [
        'cmw.employee.role.refresh' => '$refresh',
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
        return Role::query()
            ->where('guard_name', 'web')
            ->where('name', '!=', 'Super Admin')
            ->withCount('users');
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'showDetail' => Auth::user()?->can('view role'),
                    'detailHref' => route('employees.roles.user', $row->id),
                    'showEdit' => Auth::user()?->can('edit role'),
                    'editHref' => route('employees.roles.edit', $row->id),
                    'showDelete' => Auth::user()?->can('delete role'),
                    'deleteDispatchEvent' => 'cmw.employee.role.delete',
                ])),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Guard', 'guard_name')
                ->sortable()
                ->deselected(),

            Column::make('Users')
                ->label(fn ($row) => $row->users_count ?? 0)
                ->sortable(fn (Builder $query, string $direction) => $query->orderBy('users_count', $direction)),

            Column::make('Created At', 'created_at')
                ->sortable()
                ->deselected(),

            Column::make('Updated At', 'updated_at')
                ->sortable()
                ->deselected(),
        ];
    }
}
