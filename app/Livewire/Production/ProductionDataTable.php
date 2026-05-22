<?php

namespace App\Livewire\Production;

use App\Models\CMW\Transaction\OrderHeader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class ProductionDataTable extends DataTableComponent
{
    protected $model = OrderHeader::class;

    public string $mode = 'ongoing';

    protected $listeners = [
        'shp.production.order.refresh' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setDefaultSort('approved_at', 'desc');
        $this->setSearchStatus(true);
        $this->setColumnSelectStatus(true);
        $this->setPerPageAccepted([10, 25, 50, 100]);
        $this->setPerPage(25);
    }

    public function builder(): Builder
    {
        $query = OrderHeader::query()->with(['partner', 'itemCategory', 'approvedByUser']);

        return $this->mode === 'finished'
            ? $query->whereIn('status', ['FINISH', 'FINAL'])
            : $query->ongoing();
    }

    public function columns(): array
    {
        $columns = [];

        if ($this->mode === 'ongoing') {
            $columns[] = Column::make('Actions', 'id')
                ->format(fn ($value, $row) => view('components.datatables.production-action', [
                    'rowId' => $row->id,
                    'productionStatus' => $row->production_status,
                    'canEdit' => Auth::user()?->can('edit production order'),
                    'canCreateAdjustment' => Auth::user()?->can('create stock adjustment'),
                ]));
        }

        $columns = array_merge($columns, [
            Column::make('SO Code', 'code_order')
                ->sortable()
                ->searchable(),

            Column::make('SR Code', 'code_request')
                ->sortable()
                ->searchable(),

            Column::make('Date', 'date')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y')),

            Column::make('Delivery Date', 'delivery_date')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y') ?? '-'),

            Column::make('Customer', 'partner_id')
                ->sortable()
                ->searchable(fn ($query, $term) => $query->orWhereHas('partner', fn ($q) => $q->where('name', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->partner ? "{$row->partner->name} ({$row->partner->code})" : 'N/A'),

            Column::make('Category', 'item_category_id')
                ->sortable()
                ->format(fn ($value, $row) => $row->itemCategory?->name ?? 'N/A'),

            Column::make('WO Auto', 'work_order_auto')
                ->sortable()
                ->searchable()
                ->format(fn ($value) => $value ?? '-'),

            Column::make('WO Manual', 'work_order_manual')
                ->sortable()
                ->searchable()
                ->format(fn ($value) => $value ?? '-'),

            Column::make('Production Date', 'production_date')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y') ?? '-'),

            Column::make('Production Status', 'production_status')
                ->sortable()
                ->format(fn ($value) => $this->formatProductionStatus($value))
                ->html(),
        ]);

        return $columns;
    }

    private function formatProductionStatus(?string $status): string
    {
        return match ($status) {
            'ongoing' => '<span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-amber-100 text-amber-800">Ongoing</span>',
            'finish' => '<span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-green-100 text-green-800">Finish</span>',
            default => '<span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-zinc-100 text-zinc-800">'.($status ?? '-').'</span>',
        };
    }
}
