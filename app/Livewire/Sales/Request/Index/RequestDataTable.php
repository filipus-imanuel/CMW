<?php

namespace App\Livewire\Sales\Request\Index;

use App\Models\CMW\Transaction\OrderHeader;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class RequestDataTable extends DataTableComponent
{
    protected $model = OrderHeader::class;

    protected $listeners = [
        'sales.request.refresh.request' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setDefaultSort('created_at', 'desc');
        $this->setSearchStatus(true);
        $this->setColumnSelectStatus(true);
        $this->setPerPageAccepted([10, 25, 50, 100]);
        $this->setPerPage(25);
    }

    public function builder(): Builder
    {
        return OrderHeader::query()
            ->approved()
            ->with(['partner', 'itemCategory', 'createdBy', 'approvedByUser']);
    }

    public function columns(): array
    {
        return [
            Column::make('Code', 'code')
                ->sortable()
                ->searchable(),

            Column::make('Date', 'date')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y')),

            Column::make('Customer', 'partner_id')
                ->sortable()
                ->searchable(fn ($query, $term) => $query->orWhereHas('partner', fn ($q) => $q->where('name', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->partner ? "{$row->partner->name} ({$row->partner->code})" : 'N/A'),

            Column::make('Category', 'item_category_id')
                ->sortable()
                ->format(fn ($value, $row) => $row->itemCategory?->name ?? 'N/A'),

            Column::make('Total', 'total')
                ->sortable()
                ->format(fn ($value) => number_format((float) $value, 2)),

            Column::make('Approved By', 'approved_by')
                ->format(fn ($value, $row) => $row->approvedByUser?->name ?? 'N/A'),

            Column::make('Approved At', 'approved_at')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y H:i')),
        ];
    }
}
