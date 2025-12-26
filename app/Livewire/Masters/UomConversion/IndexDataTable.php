<?php

namespace App\Livewire\Masters\UomConversion;

use App\Models\CMW\Master\UomConversion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class IndexDataTable extends DataTableComponent
{
    protected $model = UomConversion::class;

    protected $listeners = [
        'cmw.master.uom-conversion.refresh' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setDefaultSort('id', 'desc');
        $this->setSearchStatus(true);
        $this->setColumnSelectStatus(true);
        $this->setPerPageAccepted([10, 25, 50, 100]);
        $this->setPerPage(25);
    }

    public function builder(): Builder
    {
        return UomConversion::query()
            ->with(['fromUom', 'toUom']);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'showEdit' => Auth::user()?->can('edit uom conversion'),
                    'editDispatchEvent' => 'cmw.master.uom-conversion.edit.open',
                    'showDelete' => Auth::user()?->can('delete uom conversion'),
                    'deleteDispatchEvent' => 'cmw.master.uom-conversion.delete',
                ])),

            Column::make('From UOM', 'from_uom_id')
                ->sortable()
                ->searchable(fn ($builder, $term) => $builder->whereHas('fromUom', fn ($query) => $query->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%")
                ))
                ->format(fn ($value, $row) => $row->fromUom ? "{$row->fromUom->name} ({$row->fromUom->code})" : '-'),

            Column::make('To UOM', 'to_uom_id')
                ->sortable()
                ->searchable(fn ($builder, $term) => $builder->whereHas('toUom', fn ($query) => $query->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%")
                ))
                ->format(fn ($value, $row) => $row->toUom ? "{$row->toUom->name} ({$row->toUom->code})" : '-'),

            Column::make('Conversion Rate', 'conversion_rate')
                ->sortable()
                ->format(fn ($value) => rtrim(rtrim(number_format($value, 6, '.', ''), '0'), '.')),

            Column::make('Formula')
                ->label(fn ($row) => ($row->fromUom && $row->toUom)
                    ? "1 {$row->fromUom->code} = ".rtrim(rtrim(number_format($row->conversion_rate, 6, '.', ''), '0'), '.')." {$row->toUom->code}"
                    : '-'),

            BooleanColumn::make('Status', 'is_active')
                ->sortable(),
        ];
    }
}
