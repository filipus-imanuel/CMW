<?php

namespace App\Livewire\Inventories\ItemPrice;

use App\Models\CMW\Inventory\CategoryPrice;
use App\Models\CMW\Inventory\ItemPrice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class IndexDataTable extends DataTableComponent
{
    protected $model = ItemPrice::class;

    protected $listeners = [
        'cmw.inventories.item-price.refresh' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('id', 'desc')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search item prices...')
            ->setColumnSelectEnabled()
            ->setEmptyMessage('No data found');
    }

    public function filters(): array
    {
        $categoryOptions = CategoryPrice::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend('All Categories', '')
            ->toArray();

        return [
            SelectFilter::make('Category')
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
        return ItemPrice::query()
            ->with(['item', 'categoryPrice', 'createdBy', 'updatedBy']);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'showEdit' => Auth::user()?->can('edit item price'),
                    'editDispatchEvent' => 'cmw.inventories.item-price.edit.open',
                    'showDelete' => Auth::user()?->can('delete item price'),
                    'deleteDispatchEvent' => 'delete',
                ])),

            Column::make('Item Code', 'item_id')
                ->sortable()
                ->searchable(fn (Builder $query, string $term) => $query->orWhereHas('item', fn ($q) => $q->where('code', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->item?->code ?? 'N/A'),

            Column::make('Item Name', 'item_id')
                ->searchable(fn (Builder $query, string $term) => $query->orWhereHas('item', fn ($q) => $q->where('name', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->item?->name ?? 'N/A'),

            Column::make('Category', 'category_price_id')
                ->sortable()
                ->searchable(fn (Builder $query, string $term) => $query->orWhereHas('categoryPrice', fn ($q) => $q->where('code', 'like', "%{$term}%")->orWhere('name', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->categoryPrice?->code ?? 'N/A'),

            Column::make('Price', 'price')
                ->sortable()
                ->format(fn ($value) => number_format($value, 2)),

            BooleanColumn::make('Status', 'is_active')
                ->sortable(),

            Column::make('Created At', 'created_at')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y H:i')),
        ];
    }
}
