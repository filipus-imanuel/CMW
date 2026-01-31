<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">System Settings</flux:heading>
    </div>

    {{-- Settings Form --}}
    <form wire:submit="update">
        <flux:card class="space-y-8">
            @forelse($this->groupedSettings as $category => $settings)
                <flux:fieldset>
                    <flux:legend>{{ ucfirst($category) }}</flux:legend>

                    <div class="space-y-6">
                        @foreach($settings as $index => $setting)
                            @if($index > 0)
                                <flux:separator variant="subtle" />
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                                <div class="md:col-span-1">
                                    <flux:heading size="sm">{{ $setting->name_en }}</flux:heading>
                                    @if($setting->description)
                                        <flux:text size="sm" class="text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $setting->description }}
                                        </flux:text>
                                    @endif
                                </div>

                                <div class="md:col-span-2">
                                    @switch($setting->data_type)
                                        @case('boolean')
                                            <flux:switch
                                                wire:model="inputs.{{ $setting->key }}"
                                                label="Enabled"
                                            />
                                            @break

                                        @case('integer')
                                            <flux:input
                                                wire:model="inputs.{{ $setting->key }}"
                                                type="number"
                                                step="1"
                                                class="max-w-xs"
                                            />
                                            @break

                                        @case('decimal')
                                            <flux:input
                                                wire:model="inputs.{{ $setting->key }}"
                                                type="number"
                                                step="0.01"
                                                class="max-w-xs"
                                            />
                                            @break

                                        @case('json')
                                            <flux:textarea
                                                wire:model="inputs.{{ $setting->key }}"
                                                rows="4"
                                                placeholder="Enter valid JSON..."
                                            />
                                            @break

                                        @default
                                            <flux:input
                                                wire:model="inputs.{{ $setting->key }}"
                                                type="text"
                                            />
                                    @endswitch
                                </div>
                            </div>
                        @endforeach
                    </div>
                </flux:fieldset>
            @empty
                <div class="text-center py-8">
                    <flux:text class="text-gray-500 dark:text-gray-400">
                        No settings available.
                    </flux:text>
                </div>
            @endforelse

            @if($this->groupedSettings->isNotEmpty())
                <div class="flex pt-4">
                    <flux:spacer />
                    <flux:button type="submit" variant="primary">
                        Save Settings
                    </flux:button>
                </div>
            @endif
        </flux:card>
    </form>
</div>
