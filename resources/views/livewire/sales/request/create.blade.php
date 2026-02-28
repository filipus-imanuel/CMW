<div>
    <div class="mb-6">
        <flux:button :href="route('sales.request.index.init')" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Draft
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-6">Create Sales Request</flux:heading>

    {{-- Warning Banners --}}
    @if(!empty($checks))
        @if(!empty($checks['debt']) && $checks['debt']['exceeded'])
            <flux:callout color="red" icon="exclamation-triangle" class="mb-4">
                <flux:callout.heading>Credit Limit Exceeded</flux:callout.heading>
                <flux:callout.text>
                    <div class="space-y-1">
                        <div>Total projected exposure: <strong>{{ number_format($checks['debt']['projected'], 2) }}</strong> exceeds credit limit: <strong>{{ number_format($checks['debt']['limit'], 2) }}</strong></div>
                        <div class="text-sm opacity-75">
                            AR Outstanding: {{ number_format($checks['debt']['outstanding'], 2) }}
                            · Pending Orders: {{ number_format($checks['debt']['pending_orders'], 2) }}
                        </div>
                    </div>
                    This request will require approval.
                </flux:callout.text>
            </flux:callout>
        @elseif(!empty($checks['debt']) && $checks['debt']['limit'] > 0)
            <flux:callout color="blue" icon="information-circle" class="mb-4">
                <flux:callout.heading>Credit Info</flux:callout.heading>
                <flux:callout.text>
                    Credit remaining: <strong>{{ number_format($checks['debt']['remaining'], 2) }}</strong> / {{ number_format($checks['debt']['limit'], 2) }}
                    <span class="text-sm opacity-75">
                        (AR: {{ number_format($checks['debt']['outstanding'], 2) }}
                        · Pending: {{ number_format($checks['debt']['pending_orders'], 2) }})
                    </span>
                </flux:callout.text>
            </flux:callout>
        @endif

        @if(!empty($checks['deliveries']) && $checks['deliveries'] > 0)
            <flux:callout color="amber" icon="exclamation-triangle" class="mb-4">
                <flux:callout.heading>Pending Deliveries</flux:callout.heading>
                <flux:callout.text>
                    This customer has {{ $checks['deliveries'] }} pending delivery/order(s).
                    This request will require approval.
                </flux:callout.text>
            </flux:callout>
        @endif

        @if(!empty($checks['limit']) && $checks['limit']['exceeded'])
            <flux:callout color="red" icon="exclamation-triangle" class="mb-4">
                <flux:callout.heading>Company Sales Limit Exceeded</flux:callout.heading>
                <flux:callout.text>
                    Current total: {{ number_format($checks['limit']['current'], 2) }} / Limit: {{ number_format($checks['limit']['limit'], 2) }}.
                    @if($checks['limit']['reason'])
                        {{ $checks['limit']['reason'] }}
                    @endif
                    This request will require approval.
                </flux:callout.text>
            </flux:callout>
        @endif
    @endif

    <flux:card>
        <form wire:submit="store">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:select
                    wire:model.live="inputs.partner_id"
                    label="Customer"
                    badge="Required"
                    placeholder="Select customer..."
                    :error="$errors->first('inputs.partner_id')"
                >
                    @foreach($dropdown_data['customers'] ?? [] as $customer)
                        <flux:select.option value="{{ $customer['value'] }}">{{ $customer['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select
                    wire:model.live="inputs.company_id"
                    label="Company"
                    badge="Required"
                    placeholder="Select company..."
                    :error="$errors->first('inputs.company_id')"
                >
                    @foreach($dropdown_data['companies'] ?? [] as $company)
                        <flux:select.option value="{{ $company['value'] }}">{{ $company['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select
                    wire:model.live="inputs.item_category_id"
                    label="Item Category"
                    badge="Required"
                    placeholder="Select item category..."
                    :error="$errors->first('inputs.item_category_id')"
                >
                    @foreach($dropdown_data['item_categories'] ?? [] as $category)
                        <flux:select.option value="{{ $category['value'] }}">{{ $category['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:date-picker
                    wire:model="inputs.date"
                    label="Date"
                    badge="Required"
                    :error="$errors->first('inputs.date')"
                />

                <div>
                    <flux:radio.group wire:model.live="inputs.tax_mode" label="Tax Mode" badge="Required" variant="segmented">
                        <flux:radio value="INCLUDE" label="Include" />
                        <flux:radio value="EXCLUDE" label="Exclude" />
                        <flux:radio value="NONE" label="No Tax" />
                    </flux:radio.group>
                    @error('inputs.tax_mode')
                        <flux:text class="text-sm text-red-500 mt-1">{{ $message }}</flux:text>
                    @enderror
                </div>

                @if($inputs['tax_mode'] !== 'NONE')
                    <flux:select
                        wire:model="inputs.tax_id"
                        label="Tax"
                        badge="Required"
                        placeholder="Select tax..."
                        :error="$errors->first('inputs.tax_id')"
                    >
                        @foreach($dropdown_data['taxes'] ?? [] as $tax)
                            <flux:select.option value="{{ $tax['value'] }}">{{ $tax['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                @endif

                <div class="md:col-span-2">
                    <flux:textarea
                        wire:model="inputs.remarks"
                        label="Remarks"
                        placeholder="Optional notes..."
                        rows="3"
                        :error="$errors->first('inputs.remarks')"
                    />
                </div>
            </div>

            {{-- Return Items Available --}}
            @if(count($returnItems) > 0)
                <div class="md:col-span-2 mt-2">
                    <flux:separator class="mb-4 mt-4" />
                    <flux:heading size="lg" class="mb-2">Return Items Available</flux:heading>
                    <flux:subheading class="mb-4">These items are available from finished sales returns for the same customer. Checked items will be automatically added to the request.</flux:subheading>

                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column class="w-8"></flux:table.column>
                            <flux:table.column>Return Code</flux:table.column>
                            <flux:table.column>Item Code</flux:table.column>
                            <flux:table.column>Item Name</flux:table.column>
                            <flux:table.column class="text-center">Qty Return</flux:table.column>
                            <flux:table.column>UOM</flux:table.column>
                            <flux:table.column>Original SO</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($returnItems as $ri)
                                <flux:table.row>
                                    <flux:table.cell>
                                        <flux:checkbox
                                            wire:click="toggleReturnItem({{ $ri['return_detail_id'] }})"
                                            :checked="in_array($ri['return_detail_id'], $selectedReturnItems)"
                                        />
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $ri['return_code'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $ri['item_code'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $ri['item_name'] }}</flux:table.cell>
                                    <flux:table.cell class="text-right tabular-nums">{{ number_format($ri['quantity_next_so'], 2) }}</flux:table.cell>
                                    <flux:table.cell>{{ $ri['uom_name'] }}</flux:table.cell>
                                    <flux:table.cell>{{ $ri['original_so_code'] }}</flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            @endif

            <div class="flex gap-2 mt-6">
                <flux:spacer/>
                <flux:button :href="route('sales.request.index.init')" variant="ghost" wire:navigate>Cancel</flux:button>
                <flux:button type="submit" variant="primary">Create & Continue</flux:button>
            </div>
        </form>
    </flux:card>
</div>
