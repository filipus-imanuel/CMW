<?php

namespace App\Livewire\Sales\Request;

use App\Helpers\CMW\CodeGeneratorHelper;
use App\Helpers\CMW\CustomerCheckHelper;
use App\Helpers\CMW\PopulateDataHelper;
use App\Models\CMW\Master\Company;
use App\Models\CMW\Master\Partner;
use App\Models\CMW\Master\Tax;
use App\Models\CMW\Transaction\OrderHeader;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Sales Request')]
class Create extends Component
{
    public $inputs = [];

    public $dropdown_data = [];

    public $checks = [];

    public function rules(): array
    {
        return [
            'inputs.partner_id' => 'required|exists:partners,id',
            'inputs.company_id' => 'required|exists:companies,id',
            'inputs.item_category_id' => 'required|exists:item_categories,id',
            'inputs.tax_mode' => 'required|in:INCLUDE,EXCLUDE,NONE',
            'inputs.tax_id' => 'nullable|required_if:inputs.tax_mode,INCLUDE,EXCLUDE|exists:taxes,id',
            'inputs.date' => 'required|date',
            'inputs.remarks' => 'nullable|string|max:1024',
        ];
    }

    public function mount(): void
    {
        $this->authorize('create sales request');

        $this->inputs = [
            'partner_id' => '',
            'company_id' => '',
            'item_category_id' => '',
            'tax_mode' => 'NONE',
            'tax_id' => '',
            'date' => now()->format('Y-m-d'),
            'remarks' => '',
        ];

        $this->loadDropdownData();
    }

    public function loadDropdownData(): void
    {
        $user = Auth::user();

        // Customers: filtered by PIC (user_id) for non-super-admin
        if ($user->hasRole('Super Admin')) {
            $this->dropdown_data['customers'] = PopulateDataHelper::getCustomers([
                'useCache' => false,
            ]);
        } else {
            $this->dropdown_data['customers'] = PopulateDataHelper::get(Partner::class, [
                'filters' => ['is_customer' => true, 'user_id' => $user->id],
                'useCache' => false,
            ]);
        }

        $this->dropdown_data['companies'] = PopulateDataHelper::getCompanies();
        $this->dropdown_data['taxes'] = PopulateDataHelper::getTaxes();
    }

    /**
     * Reset tax selection when tax mode changes.
     */
    public function updatedInputsTaxMode($value): void
    {
        if ($value === 'NONE') {
            $this->inputs['tax_id'] = '';
        }
    }

    /**
     * Load item categories based on selected company.
     */
    public function updatedInputsCompanyId($value): void
    {
        $this->dropdown_data['item_categories'] = [];
        $this->inputs['item_category_id'] = '';

        if (! $value) {
            return;
        }

        $company = Company::with('itemCategories')->find($value);

        if ($company) {
            $this->dropdown_data['item_categories'] = $company->itemCategories
                ->where('is_active', true)
                ->map(fn ($cat) => ['value' => $cat->id, 'label' => "{$cat->code} - {$cat->name}"])
                ->values()
                ->toArray();
        }
    }

    /**
     * Run customer checks when customer or category changes.
     */
    public function runChecks(): void
    {
        $this->checks = [];

        $partnerId = $this->inputs['partner_id'] ?? null;
        $companyId = $this->inputs['company_id'] ?? null;
        $categoryId = $this->inputs['item_category_id'] ?? null;

        if (! $partnerId) {
            return;
        }

        if ($partnerId && $companyId && $categoryId) {
            $this->checks = CustomerCheckHelper::runAllChecks(
                (int) $partnerId,
                (int) $companyId,
                (int) $categoryId
            );
        } else {
            // Run partial checks if only customer is selected
            $debt = CustomerCheckHelper::hasExcessiveDebt((int) $partnerId);
            $deliveries = CustomerCheckHelper::getPendingDeliveries((int) $partnerId);

            $this->checks = [
                'debt' => $debt,
                'deliveries' => $deliveries,
                'limit' => null,
                'has_issues' => $debt['exceeded'] || $deliveries > 0,
            ];
        }
    }

    public function updatedInputsPartnerId(): void
    {
        $this->runChecks();
    }

    public function updatedInputsItemCategoryId(): void
    {
        $this->runChecks();
    }

    public function store(): void
    {
        $this->authorize('create sales request');
        $validated = $this->validate();

        $order = DB::transaction(function () use ($validated) {
            // Run checks to determine initial status
            $checks = CustomerCheckHelper::runAllChecks(
                (int) $validated['inputs']['partner_id'],
                (int) $validated['inputs']['company_id'],
                (int) $validated['inputs']['item_category_id']
            );

            // Get company currency
            $company = Company::with('currency')->findOrFail($validated['inputs']['company_id']);

            // Resolve tax rate from selected tax
            $taxRate = 0;
            $taxMode = $validated['inputs']['tax_mode'];
            if ($taxMode !== 'NONE' && ! empty($validated['inputs']['tax_id'])) {
                $tax = Tax::find($validated['inputs']['tax_id']);
                $taxRate = $tax ? (float) $tax->rate : 0;
            }

            $order = OrderHeader::create([
                'code' => CodeGeneratorHelper::generateOrderCode('SR'),
                'date' => $validated['inputs']['date'],
                'currency_id' => $company->currency_id,
                'partner_id' => $validated['inputs']['partner_id'],
                'company_id' => $validated['inputs']['company_id'],
                'item_category_id' => $validated['inputs']['item_category_id'],
                'tax_mode' => $taxMode,
                'tax_id' => $taxMode !== 'NONE' ? $validated['inputs']['tax_id'] : null,
                'tax_rate' => $taxRate,
                'status' => 'INIT',
                'remarks' => $validated['inputs']['remarks'] ?? null,
                'subtotal' => 0,
                'discount' => 0,
                'tax' => 0,
                'total' => 0,
                'created_by' => Auth::id(),
            ]);

            return $order;
        });

        Flux::toast('Sales request created successfully', variant: 'success', position: 'top-end');
        $this->redirectRoute('sales.request.edit', ['id' => $order->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.sales.request.create');
    }
}
