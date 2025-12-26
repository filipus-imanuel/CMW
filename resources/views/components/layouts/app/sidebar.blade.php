<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Platform')" expandable :expanded="true" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Master')" expandable :expanded="true" class="grid">
                    <flux:navlist.item icon="globe-alt" :href="route('masters.countries.index')" :current="request()->routeIs('masters.countries.*')" wire:navigate>{{ __('Countries') }}</flux:navlist.item>
                    <flux:navlist.item icon="briefcase" :href="route('masters.positions.index')" :current="request()->routeIs('masters.positions.*')" wire:navigate>{{ __('Positions') }}</flux:navlist.item>
                    <flux:navlist.item icon="users" :href="route('masters.employees.index')" :current="request()->routeIs('masters.employees.*')" wire:navigate>{{ __('Employees') }}</flux:navlist.item>
                    <flux:navlist.item icon="user-group" :href="route('masters.user-groups.index')" :current="request()->routeIs('masters.user-groups.*')" wire:navigate>{{ __('User Groups') }}</flux:navlist.item>
                    <flux:navlist.item icon="cube" :href="route('masters.uoms.index')" :current="request()->routeIs('masters.uoms.*')" wire:navigate>{{ __('UOMs') }}</flux:navlist.item>
                    <flux:navlist.item icon="arrows-right-left" :href="route('masters.uom-conversions.index')" :current="request()->routeIs('masters.uom-conversions.*')" wire:navigate>{{ __('UOM Conversions') }}</flux:navlist.item>
                    <flux:navlist.item icon="calculator" :href="route('masters.taxes.index')" :current="request()->routeIs('masters.taxes.*')" wire:navigate>{{ __('Taxes') }}</flux:navlist.item>
                    <flux:navlist.item icon="credit-card" :href="route('masters.credit-terms.index')" :current="request()->routeIs('masters.credit-terms.*')" wire:navigate>{{ __('Credit Terms') }}</flux:navlist.item>
                    <flux:navlist.item icon="building-office" :href="route('masters.warehouses.index')" :current="request()->routeIs('masters.warehouses.*')" wire:navigate>{{ __('Warehouses') }}</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Partners')" expandable :expanded="true" class="grid">
                    <flux:navlist.item icon="truck" :href="route('partners.suppliers.index')" :current="request()->routeIs('partners.suppliers.*')" wire:navigate>{{ __('Suppliers') }}</flux:navlist.item>
                    <flux:navlist.item icon="user-group" :href="route('partners.customers.index')" :current="request()->routeIs('partners.customers.*')" wire:navigate>{{ __('Customers') }}</flux:navlist.item>
                    <flux:navlist.item icon="map-pin" :href="route('partners.customer-addresses.index')" :current="request()->routeIs('partners.customer-addresses.*')" wire:navigate>{{ __('Customer Addresses') }}</flux:navlist.item>
                    <flux:navlist.item icon="map-pin" :href="route('partners.supplier-addresses.index')" :current="request()->routeIs('partners.supplier-addresses.*')" wire:navigate>{{ __('Supplier Addresses') }}</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Inventory')" expandable :expanded="true" class="grid">
                    <flux:navlist.item icon="cube-transparent" :href="route('inventories.items.index')" :current="request()->routeIs('inventories.items.*')" wire:navigate>{{ __('Items') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <flux:navlist variant="outline">
                <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                {{ __('Repository') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                {{ __('Documentation') }}
                </flux:navlist.item>
            </flux:navlist>

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                    data-test="sidebar-menu-button"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
