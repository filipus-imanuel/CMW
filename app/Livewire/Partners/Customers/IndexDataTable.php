<?php

namespace App\Livewire\Partners\Customers;

use App\Models\CMW\Master\Partner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class IndexDataTable extends DataTableComponent
{
    protected $model = Partner::class;

    protected $listeners = [
        'cmw.partners.customers.refresh' => '$refresh',
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
        return Partner::query()->where('is_customer', true);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'showEdit' => Auth::user()?->can('edit customer'),
                    'editDispatchEvent' => 'cmw.partners.customers.edit.open',
                    'showDelete' => Auth::user()?->can('delete customer'),
                    'deleteDispatchEvent' => 'delete',
                ])),

            Column::make('Code', 'code')
                ->sortable()
                ->searchable(),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            BooleanColumn::make('Status', 'is_active')
                ->sortable(),
        ];
    }

    public function delete($id)
    {
        $this->authorize('delete customer');

        $customer = Partner::where('is_customer', true)->findOrFail($id);
        $customer->delete();

        $this->dispatch('cmw.partners.customers.refresh');
    }
}
