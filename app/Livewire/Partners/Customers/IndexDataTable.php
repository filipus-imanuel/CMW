<?php

namespace App\Livewire\Partners\Customers;

use App\Models\CMW\Inventory\CategoryPrice;
use App\Models\CMW\Master\Partner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

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

    public function filters(): array
    {
        $categoryOptions = CategoryPrice::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->pluck('name', 'id')
            ->prepend('All Categories', '')
            ->toArray();

        return [
            SelectFilter::make('Category Price')
                ->options($categoryOptions)
                ->filter(function (Builder $builder, string $value) {
                    if ($value) {
                        $builder->where('category_price_id', $value);
                    }
                }),
        ];
    }

    public function builder(): Builder
    {
        return Partner::query()
            ->with(['categoryPrice', 'companies'])
            ->where('is_customer', true);
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

            Column::make('Category Price', 'category_price_id')
                ->sortable()
                ->format(fn ($value, $row) => $row->categoryPrice?->code ?? '-'),

            Column::make('Companies', 'id')
                ->format(fn ($value, $row) => $row->companies->isNotEmpty()
                    ? $row->companies->pluck('code')->join(', ')
                    : '-'
                ),

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
