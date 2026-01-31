<?php

namespace App\Livewire\Inventories\ItemPrice;

use App\Models\CMW\Inventory\CategoryPrice;
use App\Models\CMW\Inventory\PendingItemPrice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateRangeFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class ApprovalHistoryDataTable extends DataTableComponent
{
    protected $model = PendingItemPrice::class;

    protected $listeners = [
        'cmw.inventories.item-price.approval-history.refresh' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('reviewed_at', 'desc')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search approval history...')
            ->setColumnSelectEnabled()
            ->setEmptyMessage('No approval history found');
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
            SelectFilter::make('Status')
                ->options([
                    '' => 'All Statuses',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])
                ->filter(function (Builder $builder, string $value) {
                    if ($value) {
                        $builder->where('status', $value);
                    }
                }),

            SelectFilter::make('Category')
                ->options($categoryOptions)
                ->filter(function (Builder $builder, string $value) {
                    if ($value) {
                        $builder->where('category_price_id', $value);
                    }
                }),

            DateRangeFilter::make('Reviewed Date')
                ->config([
                    'allowInput' => true,
                    'placeholder' => 'Select date range',
                ])
                ->filter(function (Builder $builder, array $value) {
                    if (isset($value['minDate']) && $value['minDate']) {
                        $builder->whereDate('reviewed_at', '>=', $value['minDate']);
                    }
                    if (isset($value['maxDate']) && $value['maxDate']) {
                        $builder->whereDate('reviewed_at', '<=', $value['maxDate']);
                    }
                }),
        ];
    }

    public function builder(): Builder
    {
        return PendingItemPrice::query()
            ->with(['item', 'categoryPrice', 'submittedBy', 'approvedBy'])
            ->whereIn('status', ['approved', 'rejected']);
    }

    public function columns(): array
    {
        return [
            Column::make('Reviewed At', 'reviewed_at')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y H:i')),

            Column::make('Status', 'status')
                ->sortable()
                ->format(fn ($value) => view('components.datatables.approval-status-badge', ['status' => $value])),

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

            Column::make('Old Price', 'old_price')
                ->sortable()
                ->format(fn ($value) => number_format($value, 2)),

            Column::make('New Price', 'new_price')
                ->sortable()
                ->format(fn ($value) => number_format($value, 2)),

            Column::make('Change', 'change_percentage')
                ->sortable()
                ->format(function ($value, $row) {
                    $pct = (float) $value;
                    $formatted = number_format(abs($pct), 2).'%';

                    if ($pct > 0) {
                        return '<span class="text-green-600 dark:text-green-400">+'.$formatted.'</span>';
                    } elseif ($pct < 0) {
                        return '<span class="text-red-600 dark:text-red-400">-'.$formatted.'</span>';
                    }

                    return '<span class="text-zinc-500">0.00%</span>';
                })
                ->html(),

            Column::make('Submitted By', 'submitted_by')
                ->sortable()
                ->searchable(fn (Builder $query, string $term) => $query->orWhereHas('submittedBy', fn ($q) => $q->where('name', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->submittedBy?->name ?? 'N/A'),

            Column::make('Submitted At', 'submitted_at')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y H:i')),

            Column::make('Reviewed By', 'approved_by')
                ->sortable()
                ->searchable(fn (Builder $query, string $term) => $query->orWhereHas('approvedBy', fn ($q) => $q->where('name', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->approvedBy?->name ?? 'N/A'),

            Column::make('Notes', 'approval_notes')
                ->searchable()
                ->format(fn ($value) => $value ? '<span class="text-sm text-zinc-600 dark:text-zinc-400" title="'.e($value).'">'.e(Str::limit($value, 30)).'</span>' : '-')
                ->html(),
        ];
    }
}
