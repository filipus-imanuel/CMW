<?php

namespace App\Livewire\Masters\CreditTerm;

use App\Models\CMW\Master\CreditTerm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class IndexDataTable extends DataTableComponent
{
    protected $model = CreditTerm::class;

    protected $listeners = [
        'cmw.master.credit-term.refresh' => '$refresh',
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
        return CreditTerm::query()
            ->with(['partnerAddress.partner']);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'showEdit' => Auth::user()?->can('edit credit term'),
                    'editDispatchEvent' => 'cmw.master.credit-term.edit.open',
                    'showDelete' => Auth::user()?->can('delete credit term'),
                    'deleteDispatchEvent' => 'cmw.master.credit-term.delete',
                ])),

            Column::make('Code', 'code')
                ->sortable()
                ->searchable(),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Partner', 'partnerAddress.partner.name')
                ->sortable()
                ->searchable()
                ->format(fn ($value, $row) => $row->partnerAddress
                    ? "{$row->partnerAddress->partner->name} - {$row->partnerAddress->label}"
                    : '-'
                ),

            Column::make('Days', 'days')
                ->sortable()
                ->format(fn ($value) => $value.' days'),

            BooleanColumn::make('Status', 'is_active')
                ->sortable(),
        ];
    }
}
