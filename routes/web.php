<?php

use App\Http\Controllers\PdfController;
use App\Livewire\Employees\Role\Create as EmployeeRoleCreate;
use App\Livewire\Employees\Role\Edit as EmployeeRoleEdit;
use App\Livewire\Employees\Role\Index as EmployeeRoleIndex;
use App\Livewire\Employees\Role\User as EmployeeRoleUser;
use App\Livewire\Employees\User\Create as EmployeeUserCreate;
use App\Livewire\Employees\User\Edit as EmployeeUserEdit;
use App\Livewire\Employees\User\Index as EmployeeUserIndex;
use App\Livewire\Inventories\Adjustment\Create as AdjustmentCreate;
use App\Livewire\Inventories\Adjustment\Edit as AdjustmentEdit;
use App\Livewire\Inventories\Adjustment\Index as AdjustmentIndex;
use App\Livewire\Inventories\Adjustment\Show as AdjustmentShow;
use App\Livewire\Inventories\CategoryPrice\Index as CategoryPriceIndex;
use App\Livewire\Inventories\HistoryItemPrice\Index as HistoryItemPriceIndex;
use App\Livewire\Inventories\Item\Create as ItemCreate;
use App\Livewire\Inventories\Item\Edit as ItemEdit;
use App\Livewire\Inventories\Item\Index as ItemIndex;
use App\Livewire\Inventories\ItemCategory\Index as ItemCategoryIndex;
use App\Livewire\Inventories\ItemPrice\Approval as ItemPriceApproval;
use App\Livewire\Inventories\ItemPrice\ApprovalHistory as ItemPriceApprovalHistory;
use App\Livewire\Inventories\ItemPrice\Index as ItemPriceIndex;
use App\Livewire\Masters\Company\Index as CompanyIndex;
use App\Livewire\Masters\Country\Index as CountryIndex;
use App\Livewire\Masters\CreditTerm\Index as CreditTermIndex;
use App\Livewire\Masters\Currency\Index as CurrencyIndex;
use App\Livewire\Masters\Department\Index as DepartmentIndex;
use App\Livewire\Masters\Employee\Index as EmployeeIndex;
use App\Livewire\Masters\ExchangeRate\Index as ExchangeRateIndex;
use App\Livewire\Masters\PaymentMethod\Index as PaymentMethodIndex;
use App\Livewire\Masters\Tax\Index as TaxIndex;
use App\Livewire\Masters\Uom\Index as UomIndex;
use App\Livewire\Masters\UomConversion\Index as UomConversionIndex;
use App\Livewire\Masters\UserGroup\Index as UserGroupIndex;
use App\Livewire\Masters\Warehouse\Index as WarehouseIndex;
use App\Livewire\Partners\CustomerAddresses\Index as CustomerAddressIndex;
use App\Livewire\Partners\Customers\Index as CustomerIndex;
use App\Livewire\Partners\SupplierAddresses\Index as SupplierAddressIndex;
use App\Livewire\Partners\Suppliers\Index as SupplierIndex;
use App\Livewire\Production\Edit as ProductionEdit;
use App\Livewire\Production\Finished as ProductionFinished;
use App\Livewire\Production\Index as ProductionIndex;
use App\Livewire\Sales\Approval\Index as SalesOrderApprovalIndex;
use App\Livewire\Sales\Approval\Show as SalesOrderApprovalShow;
use App\Livewire\Sales\Invoice\Index\Paid as InvoicePaidIndex;
use App\Livewire\Sales\Invoice\Index\Unpaid as InvoiceUnpaidIndex;
use App\Livewire\Sales\Invoice\Show as InvoiceShow;
use App\Livewire\Sales\Order\Index\Cancelled as SalesOrderCancelledIndex;
use App\Livewire\Sales\Order\Index\Ongoing as SalesOrderOngoingIndex;
use App\Livewire\Sales\Order\Index\Rejected as SalesOrderRejectedIndex;
use App\Livewire\Sales\Order\Show as SalesOrderShow;
use App\Livewire\Sales\Payment\Create as PaymentCreate;
use App\Livewire\Sales\Payment\Index\Active as PaymentActiveIndex;
use App\Livewire\Sales\Payment\Index\Cancelled as PaymentCancelledIndex;
use App\Livewire\Sales\Payment\Show as PaymentShow;
use App\Livewire\Sales\Request\Create as SalesRequestCreate;
use App\Livewire\Sales\Request\DeliverySchedule as SalesRequestDeliverySchedule;
use App\Livewire\Sales\Request\Edit as SalesRequestEdit;
use App\Livewire\Sales\Request\Index\Init as SalesRequestInitIndex;
use App\Livewire\Sales\Request\Search as SalesRequestSearch;
use App\Livewire\Sales\Return\Create as SalesReturnCreate;
use App\Livewire\Sales\Return\Edit as SalesReturnEdit;
use App\Livewire\Sales\Return\Index\Approval as SalesReturnApprovalIndex;
use App\Livewire\Sales\Return\Index\Cancelled as SalesReturnCancelledIndex;
use App\Livewire\Sales\Return\Index\Draft as SalesReturnDraftIndex;
use App\Livewire\Sales\Return\Index\Finish as SalesReturnFinishIndex;
use App\Livewire\Sales\Return\Index\Ongoing as SalesReturnOngoingIndex;
use App\Livewire\Sales\Return\Index\Rejected as SalesReturnRejectedIndex;
use App\Livewire\Sales\Return\Show as SalesReturnShow;
use App\Livewire\System\Setting\Edit as SystemSettingEdit;
use App\Livewire\Warehouses\Delivery\Cancelled\Index as DeliveryCancelledIndex;
use App\Livewire\Warehouses\Delivery\Cancelled\Show as DeliveryCancelledShow;
use App\Livewire\Warehouses\Delivery\Create as DeliveryCreate;
use App\Livewire\Warehouses\Delivery\Finish\Index as DeliveryFinishIndex;
use App\Livewire\Warehouses\Delivery\Finish\Show as DeliveryFinishShow;
use App\Livewire\Warehouses\Delivery\Ongoing\Index as DeliveryOngoingIndex;
use App\Livewire\Warehouses\Delivery\Ongoing\Show as DeliveryOngoingShow;
use App\Livewire\Warehouses\Delivery\Ongoing\So as DeliveryOngoingSo;
use App\Livewire\Warehouses\Delivery\Upcoming\Index as DeliveryUpcomingIndex;
use App\Livewire\Warehouses\Delivery\Upcoming\Show as DeliveryUpcomingShow;
use App\Livewire\Warehouses\Return\Index as WarehouseReturnIndex;
use App\Livewire\Warehouses\Return\Show as WarehouseReturnShow;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    Route::prefix('cmw')->group(function () {
        Route::prefix('masters')->name('masters.')->group(function () {
            Route::get('companies', CompanyIndex::class)->name('companies.index');
            Route::get('countries', CountryIndex::class)->name('countries.index');
            Route::get('credit-terms', CreditTermIndex::class)->name('credit-terms.index');
            Route::get('currencies', CurrencyIndex::class)->name('currencies.index');
            Route::get('employees', EmployeeIndex::class)->name('employees.index');
            Route::get('exchange-rates', ExchangeRateIndex::class)->name('exchange-rates.index');
            Route::get('departments', DepartmentIndex::class)->name('departments.index');
            Route::get('payment-methods', PaymentMethodIndex::class)->name('payment-methods.index');
            Route::get('taxes', TaxIndex::class)->name('taxes.index');
            Route::get('uom-conversions', UomConversionIndex::class)->name('uom-conversions.index');
            Route::get('uoms', UomIndex::class)->name('uoms.index');
            Route::get('user-groups', UserGroupIndex::class)->name('user-groups.index');
            Route::get('warehouses', WarehouseIndex::class)->name('warehouses.index');
        });

        Route::prefix('partners')->name('partners.')->group(function () {
            Route::get('suppliers', SupplierIndex::class)->name('suppliers.index');
            Route::get('customers', CustomerIndex::class)->name('customers.index');
            Route::get('customer-addresses', CustomerAddressIndex::class)->name('customer-addresses.index');
            Route::get('supplier-addresses', SupplierAddressIndex::class)->name('supplier-addresses.index');
        });

        Route::prefix('inventories')->name('inventories.')->group(function () {
            Route::get('category-prices', CategoryPriceIndex::class)->name('category-prices.index');
            Route::get('item-categories', ItemCategoryIndex::class)->name('item-categories.index');
            Route::get('item-price-approval-history', ItemPriceApprovalHistory::class)->name('item-price-approval-history.index');
            Route::get('item-price-approvals', ItemPriceApproval::class)->name('item-price-approvals.index');
            Route::get('item-price-history', HistoryItemPriceIndex::class)->name('item-price-history.index');
            Route::get('item-prices', ItemPriceIndex::class)->name('item-prices.index');
            Route::prefix('items')->name('items.')->group(function () {
                Route::get('/', ItemIndex::class)->name('index');
                Route::get('/create', ItemCreate::class)->name('create');
                Route::get('/{id}/edit', ItemEdit::class)->name('edit');
            });

            Route::prefix('stock-adjustments')->name('stock-adjustments.')->group(function () {
                Route::get('/', AdjustmentIndex::class)->name('index');
                Route::get('/create', AdjustmentCreate::class)->name('create');
                Route::get('/{id}/edit', AdjustmentEdit::class)->name('edit');
                Route::get('/{id}', AdjustmentShow::class)->name('show');
            });
        });

        Route::prefix('system')->name('system.')->group(function () {
            Route::get('settings', SystemSettingEdit::class)->name('settings.edit');
        });

        Route::prefix('warehouses')->name('warehouses.')->group(function () {
            Route::prefix('delivery')->name('delivery.')->group(function () {
                Route::get('/upcoming', DeliveryUpcomingIndex::class)->name('upcoming');
                Route::get('/upcoming/{id}', DeliveryUpcomingShow::class)->name('upcoming.show');
                Route::get('/create/{orderId}', DeliveryCreate::class)->name('create');
                Route::get('/{id}/pdf', [PdfController::class, 'deliveryOrder'])->name('pdf');
                Route::get('/{id}/surat-jalan', [PdfController::class, 'suratJalan'])->name('surat-jalan');

                Route::prefix('ongoing')->name('ongoing.')->group(function () {
                    Route::get('/', DeliveryOngoingIndex::class)->name('index');
                    Route::get('/so', DeliveryOngoingSo::class)->name('so');
                    Route::get('/{id}', DeliveryOngoingShow::class)->name('show');
                });

                Route::prefix('finish')->name('finish.')->group(function () {
                    Route::get('/', DeliveryFinishIndex::class)->name('index');
                    Route::get('/{id}', DeliveryFinishShow::class)->name('show');
                });

                Route::prefix('cancelled')->name('cancelled.')->group(function () {
                    Route::get('/', DeliveryCancelledIndex::class)->name('index');
                    Route::get('/{id}', DeliveryCancelledShow::class)->name('show');
                });
            });

            Route::prefix('return')->name('return.')->group(function () {
                Route::get('/', WarehouseReturnIndex::class)->name('index');
                Route::get('/{id}', WarehouseReturnShow::class)->name('show');
            });
        });

        Route::prefix('employees')->name('employees.')->group(function () {
            Route::prefix('users')->name('users.')->group(function () {
                Route::get('/', EmployeeUserIndex::class)->name('index');
                Route::get('/create', EmployeeUserCreate::class)->name('create');
                Route::get('/{id}/edit', EmployeeUserEdit::class)->name('edit');
            });

            Route::prefix('roles')->name('roles.')->group(function () {
                Route::get('/', EmployeeRoleIndex::class)->name('index');
                Route::get('/create', EmployeeRoleCreate::class)->name('create');
                Route::get('/{id}/edit', EmployeeRoleEdit::class)->name('edit');
                Route::get('/{id}/users', EmployeeRoleUser::class)->name('user');
            });
        });

        Route::prefix('sales')->name('sales.')->group(function () {
            Route::prefix('requests')->name('request.')->group(function () {
                Route::get('/', SalesRequestInitIndex::class)->name('index.init');
                Route::get('/create', SalesRequestCreate::class)->name('create');
                Route::get('/{id}/edit', SalesRequestEdit::class)->name('edit');
                Route::get('/{id}/search', SalesRequestSearch::class)->name('search');
                Route::get('/{id}/delivery-schedule', SalesRequestDeliverySchedule::class)->name('delivery-schedule');
            });

            Route::prefix('orders')->name('order.')->group(function () {
                Route::get('/', SalesOrderOngoingIndex::class)->name('index.ongoing');
                Route::get('/rejected', SalesOrderRejectedIndex::class)->name('index.rejected');
                Route::get('/cancelled', SalesOrderCancelledIndex::class)->name('index.cancelled');

                Route::prefix('approval')->name('approval.')->group(function () {
                    Route::get('/', SalesOrderApprovalIndex::class)->name('index');
                    Route::get('/{id}', SalesOrderApprovalShow::class)->name('show');
                });

                Route::get('/{id}', SalesOrderShow::class)->name('show');
                Route::get('/{id}/pdf', [PdfController::class, 'salesOrder'])->name('pdf');
            });

            Route::prefix('returns')->name('return.')->group(function () {
                Route::get('/', SalesReturnDraftIndex::class)->name('index.draft');
                Route::get('/approval', SalesReturnApprovalIndex::class)->name('index.approval');
                Route::get('/ongoing', SalesReturnOngoingIndex::class)->name('index.ongoing');
                Route::get('/finish', SalesReturnFinishIndex::class)->name('index.finish');
                Route::get('/cancelled', SalesReturnCancelledIndex::class)->name('index.cancelled');
                Route::get('/rejected', SalesReturnRejectedIndex::class)->name('index.rejected');
                Route::get('/create/{deliveryId?}', SalesReturnCreate::class)->name('create');
                Route::get('/{id}/edit', SalesReturnEdit::class)->name('edit');
                Route::get('/{id}', SalesReturnShow::class)->name('show');
            });

            Route::prefix('invoices')->name('invoice.')->group(function () {
                Route::get('/', InvoiceUnpaidIndex::class)->name('index.unpaid');
                Route::get('/paid', InvoicePaidIndex::class)->name('index.paid');
                Route::get('/{id}', InvoiceShow::class)->name('show');
            });

            Route::prefix('payments')->name('payment.')->group(function () {
                Route::get('/', PaymentActiveIndex::class)->name('index.active');
                Route::get('/cancelled', PaymentCancelledIndex::class)->name('index.cancelled');
                Route::get('/create/{invoiceId}', PaymentCreate::class)->name('create');
                Route::get('/{id}', PaymentShow::class)->name('show');
            });
        });

        Route::prefix('production')->name('production.')->group(function () {
            Route::get('/', ProductionIndex::class)->name('index');
            Route::get('/finished', ProductionFinished::class)->name('finished');
            Route::get('/{id}/edit', ProductionEdit::class)->name('edit');
        });
    });
});
