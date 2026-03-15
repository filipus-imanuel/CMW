<?php

namespace App\Livewire\Masters\PaymentMethod;

use App\Models\CMW\Master\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class IndexDataTable extends DataTableComponent
{
    protected $model = PaymentMethod::class;

    protected $listeners = [
        'cmw.master.payment-method.refresh' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('code', 'asc')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search payment methods...')
            ->setColumnSelectEnabled()
            ->setEmptyMessage('No data found');
    }

    public function builder(): Builder
    {
        return PaymentMethod::query()
            ->with(['createdBy', 'updatedBy']);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'showEdit' => Auth::user()?->can('edit payment method'),
                    'editDispatchEvent' => 'cmw.master.payment-method.edit.open',
                    'showDelete' => Auth::user()?->can('delete payment method') && ! $row->is_delete_locked,
                    'deleteDispatchEvent' => 'delete',
                ])),

            Column::make('Code', 'code')
                ->sortable()
                ->searchable(),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Remarks', 'remarks')
                ->sortable()
                ->searchable(),

            BooleanColumn::make('Status', 'is_active')
                ->sortable(),

            Column::make('Created At', 'created_at')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y H:i')),
        ];
    }
}
