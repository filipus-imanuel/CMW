<?php

namespace App\Livewire\Inventories\Adjustment;

use App\Models\CMW\Transaction\StockAdjustmentHeader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class IndexDataTable extends DataTableComponent
{
    protected $model = StockAdjustmentHeader::class;

    protected $listeners = [
        'cmw.inventories.stock-adjustment.refresh' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setDefaultSort('code', 'desc');
        $this->setSearchStatus(true);
        $this->setColumnSelectStatus(true);
        $this->setPerPageAccepted([10, 25, 50, 100]);
        $this->setPerPage(25);
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Status')
                ->options([
                    '' => 'All',
                    'draft' => 'Draft',
                    'confirmed' => 'Confirmed',
                    'cancelled' => 'Cancelled',
                ])
                ->filter(function (Builder $builder, string $value) {
                    $builder->where('status', $value);
                }),
        ];
    }

    public function builder(): Builder
    {
        return StockAdjustmentHeader::query()
            ->with(['warehouse', 'createdBy']);
    }

    public function columns(): array
    {
        return [
            Column::make('Actions', 'id')
                ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                    'rowId' => $row->id,
                    'enable_this_row' => ! $row->trashed(),
                    'showDetail' => Auth::user()?->can('view stock adjustment'),
                    'detailHref' => route('inventories.stock-adjustments.show', $row->id),
                    'showEdit' => Auth::user()?->can('edit stock adjustment') && $row->status === 'draft',
                    'editHref' => route('inventories.stock-adjustments.edit', $row->id),
                    'showDelete' => false,
                ])),

            Column::make('Code', 'code')
                ->sortable()
                ->searchable(),

            Column::make('Date', 'date')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y')),

            Column::make('Warehouse', 'warehouse_id')
                ->sortable()
                ->searchable(fn ($query, $term) => $query->orWhereHas('warehouse', fn ($q) => $q->where('name', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->warehouse?->name ?? '-'),

            Column::make('Status', 'status')
                ->sortable()
                ->format(fn ($value) => $this->formatStatus($value))
                ->html(),

            Column::make('Remarks', 'remarks')
                ->format(fn ($value) => $value ? \Illuminate\Support\Str::limit($value, 50) : '-'),

            Column::make('Created By', 'created_by')
                ->format(fn ($value, $row) => $row->createdBy?->name ?? '-'),
        ];
    }

    private function formatStatus(string $status): string
    {
        return match ($status) {
            'draft' => '<span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-yellow-100 text-yellow-800">Draft</span>',
            'confirmed' => '<span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-green-100 text-green-800">Confirmed</span>',
            'cancelled' => '<span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-red-100 text-red-800">Cancelled</span>',
            default => '<span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-zinc-100 text-zinc-800">'.$status.'</span>',
        };
    }
}
