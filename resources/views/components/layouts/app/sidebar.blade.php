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
                <flux:navlist.group :heading="__('Platform')" expandable :expanded="request()->routeIs('dashboard')" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Master')" expandable :expanded="request()->routeIs('masters.*')" class="grid">
                    <flux:navlist.item icon="building-library" :href="route('masters.companies.index')" :current="request()->routeIs('masters.companies.*')" wire:navigate>{{ __('Companies') }}</flux:navlist.item>
                    <flux:navlist.item icon="globe-alt" :href="route('masters.countries.index')" :current="request()->routeIs('masters.countries.*')" wire:navigate>{{ __('Countries') }}</flux:navlist.item>
                    <flux:navlist.item icon="credit-card" :href="route('masters.credit-terms.index')" :current="request()->routeIs('masters.credit-terms.*')" wire:navigate>{{ __('Credit Terms') }}</flux:navlist.item>
                    <flux:navlist.item icon="currency-dollar" :href="route('masters.currencies.index')" :current="request()->routeIs('masters.currencies.*')" wire:navigate>{{ __('Currencies') }}</flux:navlist.item>
                    <flux:navlist.item icon="briefcase" :href="route('masters.departments.index')" :current="request()->routeIs('masters.departments.*')" wire:navigate>{{ __('Departments') }}</flux:navlist.item>
                    <flux:navlist.item icon="users" :href="route('masters.employees.index')" :current="request()->routeIs('masters.employees.*')" wire:navigate>{{ __('Employees') }}</flux:navlist.item>
                    <flux:navlist.item icon="arrow-path" :href="route('masters.exchange-rates.index')" :current="request()->routeIs('masters.exchange-rates.*')" wire:navigate>{{ __('Exchange Rates') }}</flux:navlist.item>
                    <flux:navlist.item icon="banknotes" :href="route('masters.payment-methods.index')" :current="request()->routeIs('masters.payment-methods.*')" wire:navigate>{{ __('Payment Methods') }}</flux:navlist.item>
                    <flux:navlist.item icon="calculator" :href="route('masters.taxes.index')" :current="request()->routeIs('masters.taxes.*')" wire:navigate>{{ __('Taxes') }}</flux:navlist.item>
                    <flux:navlist.item icon="arrows-right-left" :href="route('masters.uom-conversions.index')" :current="request()->routeIs('masters.uom-conversions.*')" wire:navigate>{{ __('UOM Conversions') }}</flux:navlist.item>
                    <flux:navlist.item icon="cube" :href="route('masters.uoms.index')" :current="request()->routeIs('masters.uoms.*')" wire:navigate>{{ __('UOMs') }}</flux:navlist.item>
                    <flux:navlist.item icon="user-group" :href="route('masters.user-groups.index')" :current="request()->routeIs('masters.user-groups.*')" wire:navigate>{{ __('User Groups') }}</flux:navlist.item>
                    <flux:navlist.item icon="building-office" :href="route('masters.warehouses.index')" :current="request()->routeIs('masters.warehouses.*')" wire:navigate>{{ __('Warehouses') }}</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Partners')" expandable :expanded="request()->routeIs('partners.*')" class="grid">
                    <flux:navlist.item icon="map-pin" :href="route('partners.customer-addresses.index')" :current="request()->routeIs('partners.customer-addresses.*')" wire:navigate>{{ __('Customer Addresses') }}</flux:navlist.item>
                    <flux:navlist.item icon="user-group" :href="route('partners.customers.index')" :current="request()->routeIs('partners.customers.*')" wire:navigate>{{ __('Customers') }}</flux:navlist.item>
                    <flux:navlist.item icon="map-pin" :href="route('partners.supplier-addresses.index')" :current="request()->routeIs('partners.supplier-addresses.*')" wire:navigate>{{ __('Supplier Addresses') }}</flux:navlist.item>
                    <flux:navlist.item icon="truck" :href="route('partners.suppliers.index')" :current="request()->routeIs('partners.suppliers.*')" wire:navigate>{{ __('Suppliers') }}</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Inventory')" expandable :expanded="request()->routeIs('inventories.*')" class="grid">
                    <flux:navlist.item icon="tag" :href="route('inventories.category-prices.index')" :current="request()->routeIs('inventories.category-prices.*')" wire:navigate>{{ __('Category Prices') }}</flux:navlist.item>
                    <flux:navlist.item icon="folder" :href="route('inventories.item-categories.index')" :current="request()->routeIs('inventories.item-categories.*')" wire:navigate>{{ __('Item Categories') }}</flux:navlist.item>
                    @can('view item price approval')
                    <flux:navlist.item icon="clipboard-document-check" :href="route('inventories.item-price-approval-history.index')" :current="request()->routeIs('inventories.item-price-approval-history.*')" wire:navigate>{{ __('Item Price Approval History') }}</flux:navlist.item>
                    <flux:navlist.item icon="check-circle" :href="route('inventories.item-price-approvals.index')" :current="request()->routeIs('inventories.item-price-approvals.*')" wire:navigate>
                        <span class="flex items-center gap-2">
                            {{ __('Item Price Approvals') }}
                            <livewire:components.badges.item-price-pending-approval />
                        </span>
                    </flux:navlist.item>
                    @endcan
                    <flux:navlist.item icon="clock" :href="route('inventories.item-price-history.index')" :current="request()->routeIs('inventories.item-price-history.*')" wire:navigate>{{ __('Item Price History') }}</flux:navlist.item>
                    <flux:navlist.item icon="banknotes" :href="route('inventories.item-prices.index')" :current="request()->routeIs('inventories.item-prices.*')" wire:navigate>{{ __('Item Prices') }}</flux:navlist.item>
                    <flux:navlist.item icon="cube-transparent" :href="route('inventories.items.index')" :current="request()->routeIs('inventories.items.*')" wire:navigate>{{ __('Items') }}</flux:navlist.item>
                    @can('view stock adjustment')
                    <flux:navlist.item icon="adjustments-horizontal" :href="route('inventories.stock-adjustments.index')" :current="request()->routeIs('inventories.stock-adjustments.*')" wire:navigate>{{ __('Stock Adjustments') }}</flux:navlist.item>
                    @endcan
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Sales')" expandable :expanded="request()->routeIs('sales.*')" class="grid">
                    @can('view sales request')
                    <flux:navlist.item icon="document-text" :href="route('sales.request.index.init')" :current="request()->routeIs('sales.request.index.init') || request()->routeIs('sales.request.create') || request()->routeIs('sales.request.edit') || request()->routeIs('sales.request.search')" wire:navigate>{{ __('Sales Requests') }}</flux:navlist.item>
                    @endcan
                    @canany(['view sales order', 'approve sales order'])
                    <flux:navlist.item icon="shield-check" :href="route('sales.order.approval.index')" :current="request()->routeIs('sales.order.approval.*')" wire:navigate>{{ __('SO Approval') }}</flux:navlist.item>
                    @endcanany
                    @can('view sales order')
                    <flux:navlist.item icon="truck" :href="route('sales.order.index.ongoing')" :current="request()->routeIs('sales.order.index.ongoing') || request()->routeIs('sales.order.show')" wire:navigate>{{ __('Ongoing Orders') }}</flux:navlist.item>
                    <flux:navlist.item icon="x-circle" :href="route('sales.order.index.rejected')" :current="request()->routeIs('sales.order.index.rejected')" wire:navigate>{{ __('Rejected Orders') }}</flux:navlist.item>
                    <flux:navlist.item icon="no-symbol" :href="route('sales.order.index.cancelled')" :current="request()->routeIs('sales.order.index.cancelled')" wire:navigate>{{ __('Cancelled Orders') }}</flux:navlist.item>
                    @endcan
                    @can('view ar invoice')
                    <flux:navlist.group heading="Invoice" expandable :expanded="request()->routeIs('sales.invoice.*')" class="grid">
                        <flux:navlist.item icon="document-currency-dollar" :href="route('sales.invoice.index.unpaid')" :current="request()->routeIs('sales.invoice.index.unpaid')" wire:navigate>{{ __('Unpaid') }}</flux:navlist.item>
                        <flux:navlist.item icon="check-circle" :href="route('sales.invoice.index.paid')" :current="request()->routeIs('sales.invoice.index.paid')" wire:navigate>{{ __('Paid') }}</flux:navlist.item>
                    </flux:navlist.group>
                    @endcan
                    @can('view ar payment')
                    <flux:navlist.group heading="Payment" expandable :expanded="request()->routeIs('sales.payment.*')" class="grid">
                        <flux:navlist.item icon="banknotes" :href="route('sales.payment.index.active')" :current="request()->routeIs('sales.payment.index.active') || request()->routeIs('sales.payment.create') || request()->routeIs('sales.payment.show')" wire:navigate>{{ __('Active') }}</flux:navlist.item>
                        <flux:navlist.item icon="no-symbol" :href="route('sales.payment.index.cancelled')" :current="request()->routeIs('sales.payment.index.cancelled')" wire:navigate>{{ __('Cancelled') }}</flux:navlist.item>
                    </flux:navlist.group>
                    @endcan
                    @can('view sales return')
                    <flux:navlist.group heading="Return" expandable :expanded="request()->routeIs('sales.return.*')" class="grid">
                        <flux:navlist.item icon="document-text" :href="route('sales.return.index.draft')" :current="request()->routeIs('sales.return.index.draft') || request()->routeIs('sales.return.create') || request()->routeIs('sales.return.edit')" wire:navigate>{{ __('Draft') }}</flux:navlist.item>
                        <flux:navlist.item icon="shield-check" :href="route('sales.return.index.approval')" :current="request()->routeIs('sales.return.index.approval')" wire:navigate>{{ __('Approval') }}</flux:navlist.item>
                        <flux:navlist.item icon="arrow-path" :href="route('sales.return.index.ongoing')" :current="request()->routeIs('sales.return.index.ongoing') || request()->routeIs('sales.return.show')" wire:navigate>{{ __('Ongoing') }}</flux:navlist.item>
                        <flux:navlist.item icon="check-circle" :href="route('sales.return.index.finish')" :current="request()->routeIs('sales.return.index.finish')" wire:navigate>{{ __('Finish') }}</flux:navlist.item>
                        <flux:navlist.item icon="no-symbol" :href="route('sales.return.index.cancelled')" :current="request()->routeIs('sales.return.index.cancelled')" wire:navigate>{{ __('Cancelled') }}</flux:navlist.item>
                        <flux:navlist.item icon="x-circle" :href="route('sales.return.index.rejected')" :current="request()->routeIs('sales.return.index.rejected')" wire:navigate>{{ __('Rejected') }}</flux:navlist.item>
                    </flux:navlist.group>
                    @endcan
                </flux:navlist.group>

                @can('view delivery order')
                <flux:navlist.group :heading="__('Warehouse')" expandable :expanded="request()->routeIs('warehouses.*')" class="grid">
                    <flux:navlist.item icon="inbox-arrow-down" :href="route('warehouses.delivery.upcoming')" :current="request()->routeIs('warehouses.delivery.upcoming') || request()->routeIs('warehouses.delivery.create')" wire:navigate>{{ __('Upcoming SO') }}</flux:navlist.item>
                    <flux:navlist.item icon="clipboard-document-list" :href="route('warehouses.delivery.ongoing.so')" :current="request()->routeIs('warehouses.delivery.ongoing.so')" wire:navigate>{{ __('Ongoing SO') }}</flux:navlist.item>
                    <flux:navlist.item icon="truck" :href="route('warehouses.delivery.ongoing.index')" :current="request()->routeIs('warehouses.delivery.ongoing.index') || request()->routeIs('warehouses.delivery.ongoing.show')" wire:navigate>{{ __('Ongoing Delivery') }}</flux:navlist.item>
                    <flux:navlist.item icon="check-circle" :href="route('warehouses.delivery.finish.index')" :current="request()->routeIs('warehouses.delivery.finish.*')" wire:navigate>{{ __('Finished Delivery') }}</flux:navlist.item>
                    <flux:navlist.item icon="x-circle" :href="route('warehouses.delivery.cancelled.index')" :current="request()->routeIs('warehouses.delivery.cancelled.*')" wire:navigate>{{ __('Cancelled Delivery') }}</flux:navlist.item>
                    @can('view warehouse return')
                    <flux:navlist.item icon="arrow-uturn-left" :href="route('warehouses.return.index')" :current="request()->routeIs('warehouses.return.*')" wire:navigate>{{ __('Return') }}</flux:navlist.item>
                    @endcan
                </flux:navlist.group>
                @endcan

                @canany(['view user', 'view role'])
                <flux:navlist.group :heading="__('Employee')" expandable :expanded="request()->routeIs('employees.*')" class="grid">
                    @can('view user')
                    <flux:navlist.item icon="users" :href="route('employees.users.index')" :current="request()->routeIs('employees.users.*')" wire:navigate>{{ __('Users') }}</flux:navlist.item>
                    @endcan
                    @can('view role')
                    <flux:navlist.item icon="shield-check" :href="route('employees.roles.index')" :current="request()->routeIs('employees.roles.*')" wire:navigate>{{ __('Roles') }}</flux:navlist.item>
                    @endcan
                </flux:navlist.group>
                @endcanany

                @can('edit system setting')
                <flux:navlist.group :heading="__('System')" expandable :expanded="request()->routeIs('system.*')" class="grid">
                    <flux:navlist.item icon="cog-6-tooth" :href="route('system.settings.edit')" :current="request()->routeIs('system.settings.*')" wire:navigate>{{ __('Settings') }}</flux:navlist.item>
                </flux:navlist.group>
                @endcan
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

        @persist('toast')
            <flux:toast position="top end" />
        @endpersist

        @fluxScripts
    </body>
</html>
