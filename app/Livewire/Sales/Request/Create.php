<?php

namespace App\Livewire\Sales\Request;

use App\Helpers\CMW\CodeGeneratorHelper;
use App\Helpers\CMW\CustomerCheckHelper;
use App\Helpers\CMW\PopulateDataHelper;
use App\Models\CMW\Master\Company;
use App\Models\CMW\Master\Partner;
use App\Models\CMW\Master\Tax;
use App\Models\CMW\Transaction\OrderDetail;
use App\Models\CMW\Transaction\OrderHeader;
use App\Models\CMW\Transaction\ReturnDetail;
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

    /**
     * Available return items for the selected partner.
     */
    public $returnItems = [];

    /**
     * IDs of selected return detail items to carry over to Edit.
     */
    public $selectedReturnItems = [];

    /**
     * Whether the user can override the company's default tax.
     */
    public bool $canOverrideTax = false;

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

        $this->canOverrideTax = Auth::user()->can('override tax sales request');

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
        $this->dropdown_data['customers'] = [];
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

        $this->loadReturnItems();
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
        $this->loadReturnItems();
    }

    /**
     * Load available return items for the selected partner (preview only).
     * Only shows items matching same partner, company, item category, and tax settings.
     */
    public function loadReturnItems(): void
    {
        $partnerId = $this->inputs['partner_id'] ?? null;
        $companyId = $this->inputs['company_id'] ?? null;
        $categoryId = $this->inputs['item_category_id'] ?? null;
        $taxMode = $this->inputs['tax_mode'] ?? 'NONE';
        $taxId = $this->inputs['tax_id'] ?? null;

        if (! $partnerId || ! $companyId || ! $categoryId) {
            $this->returnItems = [];
            $this->selectedReturnItems = [];

            return;
        }

        $details = ReturnDetail::with(['header.orderHeader', 'item', 'itemUom.uom'])
            ->where('quantity_next_so', '>', 0)
            ->where('is_next_so_consumed', false)
            ->whereHas('header', function ($q) use ($partnerId, $companyId) {
                $q->where('partner_id', $partnerId)
                    ->where('company_id', $companyId)
                    ->where('status', 'FINISH')
                    ->where('return_type', 'ITEM');
            })
            ->whereHas('header.orderHeader', function ($q) use ($categoryId, $taxMode, $taxId) {
                $q->where('item_category_id', $categoryId)
                    ->where('tax_mode', $taxMode);
                if ($taxMode !== 'NONE' && $taxId) {
                    $q->where('tax_id', $taxId);
                }
            })
            ->get();

        $this->returnItems = $details->map(function ($detail) {
            return [
                'return_detail_id' => $detail->id,
                'return_code' => $detail->header?->code ?? '',
                'item_id' => $detail->item_id,
                'item_uom_id' => $detail->item_uom_id,
                'item_code' => $detail->item?->code ?? '',
                'item_name' => $detail->item?->name ?? '',
                'uom_name' => $detail->itemUom?->uom?->name ?? '',
                'quantity_next_so' => (float) $detail->quantity_next_so,
                'original_so_code' => $detail->header?->orderHeader?->code_order ?? '',
            ];
        })->toArray();

        // Clear selections that are no longer available
        $availableIds = collect($this->returnItems)->pluck('return_detail_id')->toArray();
        $this->selectedReturnItems = array_values(array_intersect($this->selectedReturnItems, $availableIds));
    }

    /**
     * Toggle a return item selection.
     */
    public function toggleReturnItem(int $detailId): void
    {
        $returnItem = collect($this->returnItems)->firstWhere('return_detail_id', $detailId);
        if (! $returnItem) {
            return;
        }

        if (in_array($detailId, $this->selectedReturnItems)) {
            $this->selectedReturnItems = array_values(array_filter($this->selectedReturnItems, fn ($id) => $id !== $detailId));
        } else {
            $this->selectedReturnItems[] = $detailId;
        }
    }

    /**
     * Reload return items when tax selection changes.
     */
    public function updatedInputsTaxId(): void
    {
        $this->loadReturnItems();
    }

    public function updatedInputsCompanyId($value): void
    {
        $this->dropdown_data['item_categories'] = [];
        $this->dropdown_data['customers'] = [];
        $this->inputs['item_category_id'] = '';
        $this->inputs['partner_id'] = '';

        if ($value) {
            $company = Company::with(['itemCategories', 'tax'])->find($value);

            if ($company) {
                // Auto-populate tax from company defaults
                $this->inputs['tax_mode'] = $company->tax_mode ?? 'NONE';
                $this->inputs['tax_id'] = $company->tax_id ?? '';

                $this->dropdown_data['item_categories'] = $company->itemCategories
                    ->where('is_active', true)
                    ->map(fn ($cat) => ['value' => $cat->id, 'label' => "{$cat->code} - {$cat->name}"])
                    ->values()
                    ->toArray();
            }

            // Load customers linked to this company
            $user = Auth::user();
            $query = Partner::whereHas('companies', fn ($q) => $q->where('companies.id', $value))
                ->where('is_customer', true)
                ->where('is_active', true);

            if (! $user->hasRole('Super Admin')) {
                $query->where('user_id', $user->id);
            }

            $this->dropdown_data['customers'] = $query
                ->orderBy('name')
                ->get()
                ->map(fn ($p) => ['value' => $p->id, 'label' => "{$p->code} - {$p->name}"])
                ->toArray();
        }

        $this->runChecks();
        $this->loadReturnItems();
    }

    public function updatedInputsItemCategoryId(): void
    {
        $this->runChecks();
        $this->loadReturnItems();
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
                'code_request' => CodeGeneratorHelper::generateOrderCode('SR'),
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

            // Create OrderDetails for selected return items directly
            if (! empty($this->selectedReturnItems)) {
                $returnDetails = ReturnDetail::with(['item', 'itemUom'])
                    ->whereIn('id', $this->selectedReturnItems)
                    ->get();

                foreach ($returnDetails as $returnDetail) {
                    OrderDetail::create([
                        'order_header_id' => $order->id,
                        'item_id' => $returnDetail->item_id,
                        'item_uom_id' => $returnDetail->item_uom_id,
                        'return_detail_id' => $returnDetail->id,
                        'quantity' => (float) $returnDetail->quantity_next_so,
                        'price_proposed' => 0,
                        'price_deal' => 0,
                        'discount' => 0,
                        'tax' => 0,
                        'total' => 0,
                        'created_by' => Auth::id(),
                    ]);
                }
            }

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
