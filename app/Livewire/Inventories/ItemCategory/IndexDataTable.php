<?php

namespace App\Livewire\Inventories\ItemCategory;

use App\Models\CMW\Inventory\ItemCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class IndexDataTable extends DataTableComponent
{
    protected $model = ItemCategory::class;

    protected $listeners = [
        'cmw.inventory.item-category.refresh' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('code', 'asc')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search item categories...')
            ->setColumnSelectEnabled()
            ->setEmptyMessage('No data found');
    }

    public function builder(): Builder
    {
        return ItemCategory::query()
            ->with(['createdBy', 'updatedBy', 'companies']);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'showEdit' => Auth::user()?->can('edit item category'),
                    'editDispatchEvent' => 'cmw.inventory.item-category.edit.open',
                    'showDelete' => Auth::user()?->can('delete item category') && ! $row->is_delete_locked,
                    'deleteDispatchEvent' => 'delete',
                ])),

            Column::make('Code', 'code')
                ->sortable()
                ->searchable(),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Companies', 'id')
                ->format(fn ($value, $row) => $row->companies->pluck('name')->join(', ') ?: '-'),

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
