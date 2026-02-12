<div>
    <flux:modal name="search-item" class="md:w-[600px]">
        <flux:heading>Search Items</flux:heading>
        <flux:subheading class="mb-4">Search by item code or name</flux:subheading>

        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Type at least 2 characters to search..."
            icon="magnifying-glass"
            autofocus
        />

        @if(count($results) > 0)
            <div class="mt-4 max-h-80 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="text-left py-2 px-2 font-medium text-zinc-500">Code</th>
                            <th class="text-left py-2 px-2 font-medium text-zinc-500">Name</th>
                            <th class="text-left py-2 px-2 font-medium text-zinc-500">UOM</th>
                            <th class="text-center py-2 px-2 font-medium text-zinc-500">Category</th>
                            <th class="text-right py-2 px-2 font-medium text-zinc-500">Price</th>
                            <th class="text-center py-2 px-2 font-medium text-zinc-500"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $item)
                            <tr wire:key="search-item-{{ $item['id'] }}" class="border-b border-zinc-100 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer">
                                <td class="py-2 px-2">{{ $item['code'] }}</td>
                                <td class="py-2 px-2">{{ $item['name'] }}</td>
                                <td class="py-2 px-2">{{ $item['uom_name'] }}</td>
                                <td class="py-2 px-2 text-center">
                                    @if($item['category_price_code'])
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ $item['category_price_code'] }}
                                        </span>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>
                                <td class="py-2 px-2 text-right">{{ number_format($item['sell_price'], 2) }}</td>
                                <td class="py-2 px-2 text-center">
                                    <flux:button
                                        wire:click="selectItem({{ $item['id'] }})"
                                        variant="primary"
                                        size="xs"
                                        icon="plus"
                                    >
                                        Add
                                    </flux:button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif(strlen($search) >= 2)
            <div class="text-center py-8 text-zinc-400 mt-4">
                <flux:text>No items found matching "{{ $search }}"</flux:text>
            </div>
        @endif
    </flux:modal>
</div>
