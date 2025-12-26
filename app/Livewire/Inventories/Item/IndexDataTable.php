<?php

namespace App\Livewire\Inventories\Item;

use App\Models\CMW\Master\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class IndexDataTable extends DataTableComponent
{
    protected $model = Item::class;

    protected $listeners = [
        'cmw.inventories.item.refresh' => '$refresh',
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
        return [
            SelectFilter::make('Type')
                ->options([
                    '' => 'All',
                    'RAW_MATERIAL' => 'Raw Material',
                    'WORK_IN_PROCESS' => 'Work In Process',
                    'FINISHED_GOOD' => 'Finished Good',
                    'SPARE_PART' => 'Spare Part',
                ])
                ->filter(function (Builder $builder, string $value) {
                    $builder->where('type', $value);
                }),
        ];
    }

    public function builder(): Builder
    {
        return Item::query()
            ->with(['uom']);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'showEdit' => Auth::user()?->can('edit item'),
                    'editDispatchEvent' => 'cmw.inventories.item.edit.open',
                    'showDelete' => Auth::user()?->can('delete item'),
                    'deleteDispatchEvent' => 'cmw.inventories.item.delete',
                ])),

            Column::make('Code', 'code')
                ->sortable()
                ->searchable(),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Type', 'type')
                ->sortable()
                ->format(fn ($value) => $this->formatType($value))
                ->html(),

            Column::make('UOM', 'uom.code')
                ->sortable()
                ->searchable(),

            Column::make('Cost Price', 'cost_price')
                ->sortable()
                ->format(fn ($value) => number_format($value, 2)),

            Column::make('Sell Price', 'sell_price')
                ->sortable()
                ->format(fn ($value) => number_format($value, 2)),

            BooleanColumn::make('Status', 'is_active')
                ->sortable(),
        ];
    }

    private function formatType($type): string
    {
        // Normalize type to uppercase with underscore
        $normalizedType = strtoupper(str_replace(['_goods', '_part', '_material', '_process'], ['_GOOD', '_PART', '_MATERIAL', '_PROCESS'], $type));

        $typeConfig = [
            'RAW_MATERIAL' => [
                'label' => 'Raw Material',
                'classes' => 'text-indigo-700 bg-indigo-100 dark:text-indigo-100 dark:bg-indigo-900',
            ],
            'WORK_IN_PROCESS' => [
                'label' => 'WIP',
                'classes' => 'text-amber-700 bg-amber-100 dark:text-amber-100 dark:bg-amber-900',
            ],
            'FINISHED_GOOD' => [
                'label' => 'Finished Good',
                'classes' => 'text-teal-700 bg-teal-100 dark:text-teal-100 dark:bg-teal-900',
            ],
            'SPARE_PART' => [
                'label' => 'Spare Part',
                'classes' => 'text-violet-700 bg-violet-100 dark:text-violet-100 dark:bg-violet-900',
            ],
        ];

        $config = $typeConfig[$normalizedType] ?? [
            'label' => ucwords(str_replace('_', ' ', strtolower($type))),
            'classes' => 'text-gray-700 bg-gray-100 dark:text-gray-100 dark:bg-gray-900',
        ];

        return "<span class='px-2 py-1 text-xs font-medium rounded-full {$config['classes']}'>{$config['label']}</span>";
    }
}
