<?php

namespace App\Livewire\Masters\ExchangeRate;

use App\Models\CMW\Master\ExchangeRate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class IndexDataTable extends DataTableComponent
{
    protected $model = ExchangeRate::class;

    protected $listeners = [
        'cmw.master.exchange-rate.refresh' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('effective_date', 'desc')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search exchange rates...')
            ->setColumnSelectEnabled()
            ->setEmptyMessage('No data found');
    }

    public function builder(): Builder
    {
        return ExchangeRate::query()
            ->with(['fromCurrency', 'toCurrency', 'createdBy', 'updatedBy']);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'showEdit' => Auth::user()?->can('edit exchange rate'),
                    'editDispatchEvent' => 'cmw.master.exchange-rate.edit.open',
                    'showDelete' => Auth::user()?->can('delete exchange rate'),
                    'deleteDispatchEvent' => 'delete',
                ])),

            Column::make('From Currency', 'from_currency_id')
                ->sortable()
                ->searchable(fn ($query, $term) => $query->orWhereHas('fromCurrency', fn ($q) => $q->where('code', 'like', "%{$term}%")->orWhere('name', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->fromCurrency ? "{$row->fromCurrency->code} - {$row->fromCurrency->name}" : '-'),

            Column::make('To Currency', 'to_currency_id')
                ->sortable()
                ->searchable(fn ($query, $term) => $query->orWhereHas('toCurrency', fn ($q) => $q->where('code', 'like', "%{$term}%")->orWhere('name', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->toCurrency ? "{$row->toCurrency->code} - {$row->toCurrency->name}" : '-'),

            Column::make('Effective Date', 'effective_date')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y')),

            Column::make('Rate', 'rate')
                ->sortable()
                ->format(fn ($value) => number_format((float) $value, 2)),

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
