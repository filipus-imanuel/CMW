<?php

namespace App\Livewire\Inventories\HistoryItemPrice;

use App\Models\CMW\History\HistoryItemPrice;
use App\Models\CMW\Inventory\CategoryPrice;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateRangeFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class IndexDataTable extends DataTableComponent
{
    protected $model = HistoryItemPrice::class;

    protected $listeners = [
        'cmw.inventories.history-item-price.refresh' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('created_at', 'desc')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search by item code or name...')
            ->setColumnSelectEnabled()
            ->setEmptyMessage('No price history found');
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
            SelectFilter::make('Category')
                ->options($categoryOptions)
                ->filter(function (Builder $builder, string $value) {
                    if ($value) {
                        $builder->where('category_price_id', $value);
                    }
                }),
            DateRangeFilter::make('Date Range')
                ->config([
                    'allowInput' => true,
                    'placeholder' => 'Select date range',
                ])
                ->filter(function (Builder $builder, array $value) {
                    if (isset($value['minDate']) && $value['minDate']) {
                        $builder->whereDate('history_item_prices.created_at', '>=', $value['minDate']);
                    }
                    if (isset($value['maxDate']) && $value['maxDate']) {
                        $builder->whereDate('history_item_prices.created_at', '<=', $value['maxDate']);
                    }
                }),
        ];
    }

    public function builder(): Builder
    {
        // Default to last 30 days
        return HistoryItemPrice::query()
            ->with(['item', 'categoryPrice', 'createdBy'])
            ->whereDate('history_item_prices.created_at', '>=', now()->subDays(30));
    }

    public function columns(): array
    {
        return [
            Column::make('Changed At', 'created_at')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y H:i')),

            Column::make('Item Code', 'item_id')
                ->sortable()
                ->searchable(fn (Builder $query, string $term) => $query->orWhereHas('item', fn ($q) => $q->where('code', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->item?->code ?? 'N/A'),

            Column::make('Item Name', 'item_id')
                ->searchable(fn (Builder $query, string $term) => $query->orWhereHas('item', fn ($q) => $q->where('name', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->item?->name ?? 'N/A'),

            Column::make('Category', 'category_price_id')
                ->sortable()
                ->searchable(fn (Builder $query, string $term) => $query->orWhereHas('categoryPrice', fn ($q) => $q->where('code', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->categoryPrice?->code ?? 'N/A'),

            Column::make('Old Price', 'old_price')
                ->sortable()
                ->format(fn ($value) => number_format($value, 2)),

            Column::make('New Price', 'new_price')
                ->sortable()
                ->format(fn ($value) => number_format($value, 2)),

            Column::make('Difference', 'id')
                ->format(function ($value, $row) {
                    $diff = (float) $row->new_price - (float) $row->old_price;
                    $formatted = number_format(abs($diff), 2);

                    if ($diff > 0) {
                        // Price increase - green (positive for business)
                        return '<span class="text-green-600 dark:text-green-400">+'.htmlspecialchars($formatted).'</span>';
                    } elseif ($diff < 0) {
                        // Price decrease - red
                        return '<span class="text-red-600 dark:text-red-400">-'.htmlspecialchars($formatted).'</span>';
                    }

                    return '<span class="text-zinc-500">0.00</span>';
                })
                ->html(),

            Column::make('Changed By', 'createdBy.name')
                ->sortable()
                ->searchable(fn (Builder $query, string $term) => $query->orWhereHas('createdBy', fn ($q) => $q->where('name', 'like', "%{$term}%")))
                ->format(fn ($value, $row) => $row->createdBy?->name ?? 'System'),
        ];
    }
}
