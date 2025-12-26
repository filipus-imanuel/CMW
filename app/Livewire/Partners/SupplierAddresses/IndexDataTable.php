<?php

namespace App\Livewire\Partners\SupplierAddresses;

use App\Models\CMW\Master\Partner;
use App\Models\CMW\Master\PartnerAddress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class IndexDataTable extends DataTableComponent
{
    protected $model = PartnerAddress::class;

    protected $listeners = [
        'cmw.partners.supplier-addresses.refresh' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setDefaultSort('partner_id', 'asc');
        $this->setSearchStatus(true);
        $this->setColumnSelectStatus(true);
        $this->setPerPageAccepted([10, 25, 50, 100]);
        $this->setPerPage(25);
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Supplier')
                ->options([
                    '' => 'All',
                    ...Partner::where('is_active', true)
                        ->where('is_supplier', true)
                        ->orderBy('name')
                        ->get()
                        ->pluck('name', 'id')
                        ->toArray(),
                ])
                ->filter(function (Builder $builder, string $value) {
                    $builder->where('partner_id', $value);
                }),
        ];
    }

    public function builder(): Builder
    {
        return PartnerAddress::query()
            ->with(['partner'])
            ->whereHas('partner', function ($query) {
                $query->where('is_supplier', true);
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'showEdit' => Auth::user()?->can('edit partner address'),
                    'editDispatchEvent' => 'cmw.partners.supplier-addresses.edit.open',
                    'showDelete' => Auth::user()?->can('delete partner address'),
                    'deleteDispatchEvent' => 'delete',
                ])),

            Column::make('Supplier', 'partner_id')
                ->sortable()
                ->searchable(fn ($query, $searchTerm) => $query->orWhereHas('partner', fn ($q) => $q->where('name', 'like', "%{$searchTerm}%")))
                ->format(fn ($value, $row) => $row->partner ? "{$row->partner->name} ({$row->partner->code})" : 'N/A'),

            Column::make('Label', 'label')
                ->sortable()
                ->searchable(),

            Column::make('Address', 'address')
                ->sortable()
                ->searchable()
                ->format(fn ($value) => strlen($value) > 50 ? substr($value, 0, 50).'...' : $value),

            Column::make('City', 'city')
                ->sortable()
                ->searchable()
                ->format(fn ($value) => $value ?: '-'),

            Column::make('Phone', 'phone')
                ->sortable()
                ->searchable()
                ->format(fn ($value) => $value ?: '-'),

            Column::make('Contact Person', 'contact_person')
                ->sortable()
                ->searchable()
                ->format(fn ($value) => $value ?: '-'),

            BooleanColumn::make('Default', 'is_default')
                ->sortable(),

            BooleanColumn::make('Status', 'is_active')
                ->sortable(),
        ];
    }
}
