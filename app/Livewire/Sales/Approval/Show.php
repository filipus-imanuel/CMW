<?php

namespace App\Livewire\Sales\Approval;

use App\Helpers\CMW\CodeGeneratorHelper;
use App\Helpers\CMW\CustomerCheckHelper;
use App\Models\CMW\Transaction\OrderHeader;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Approve Sales Order')]
class Show extends Component
{
    public ?OrderHeader $order = null;

    public $checks = [];

    public $rejection_reason = '';

    public $approvalNotes = '';

    public function mount($id): void
    {
        if (! Auth::user()?->can('view sales order') && ! Auth::user()?->can('approve sales order')) {
            abort(403);
        }

        $this->order = OrderHeader::with([
            'partner', 'company', 'itemCategory', 'currency',
            'details.item', 'details.itemUom.uom', 'createdBy',
            'deliverySchedules',
        ])->findOrFail($id);

        // Status guard — only APPROVAL status can be reviewed
        if ($this->order->status !== 'APPROVAL') {
            $this->redirectRoute('sales.order.approval.index', navigate: true);

            return;
        }

        $this->runChecks();
    }

    public function runChecks(): void
    {
        if (! $this->order) {
            return;
        }

        $this->checks = CustomerCheckHelper::runAllChecks(
            $this->order->partner_id,
            $this->order->company_id,
            $this->order->item_category_id,
            (float) $this->order->total,
            $this->order->id
        );
    }

    // ──────────────────────────────────────────────────────────────────
    // RESTORE → back to INIT (draft)
    // ──────────────────────────────────────────────────────────────────

    public function confirmRestore(): void
    {
        $this->resetValidation();
        $this->modal('restore-confirmation')->show();
    }

    public function processRestore(): void
    {
        $this->authorize('approve sales order');

        DB::transaction(function () {
            $this->order->update([
                'status' => 'INIT',
                'updated_by' => Auth::id(),
            ]);
        });

        Flux::toast('Sales request restored to draft', variant: 'success', position: 'top-end');
        $this->dispatch('shp.sales.order.refresh.approval');
        $this->dispatch('sales.request.refresh.init');
        $this->modal('restore-confirmation')->close();
        $this->redirectRoute('sales.order.approval.index', navigate: true);
    }

    // ──────────────────────────────────────────────────────────────────
    // REJECT → REJECTED
    // ──────────────────────────────────────────────────────────────────

    public function confirmReject(): void
    {
        $this->reset(['rejection_reason']);
        $this->resetValidation();
        $this->modal('reject-confirmation')->show();
    }

    public function processReject(): void
    {
        $this->authorize('reject sales order');

        $this->validate([
            'rejection_reason' => 'required|string|max:1024',
        ]);

        DB::transaction(function () {
            $this->order->update([
                'status' => 'REJECTED',
                'rejection_reason' => $this->rejection_reason,
                'updated_by' => Auth::id(),
            ]);
        });

        Flux::toast('Sales request rejected', variant: 'warning', position: 'top-end');
        $this->dispatch('shp.sales.order.refresh.approval');
        $this->dispatch('shp.sales.order.refresh.rejected');
        $this->modal('reject-confirmation')->close();
        $this->redirectRoute('sales.order.approval.index', navigate: true);
    }

    // ──────────────────────────────────────────────────────────────────
    // APPROVE → ORDER (generate code_order)
    // ──────────────────────────────────────────────────────────────────

    public function confirmApprove(): void
    {
        $this->reset(['approvalNotes']);
        $this->resetValidation();
        $this->modal('approve-confirmation')->show();
    }

    public function processApproval(): void
    {
        $this->authorize('approve sales order');

        DB::transaction(function () {
            $updateData = [
                'status' => 'ORDER',
                'code_order' => CodeGeneratorHelper::generateOrderCode('SO'),
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => null,
                'updated_by' => Auth::id(),
            ];

            if (! empty($this->approvalNotes)) {
                $updateData['remarks'] = trim($this->order->remarks."\n\n[Approval Notes]\n".$this->approvalNotes);
            }

            $this->order->update($updateData);
        });

        Flux::toast('Sales order approved successfully', variant: 'success', position: 'top-end');
        $this->dispatch('shp.sales.order.refresh.approval');
        $this->dispatch('shp.sales.order.refresh.ongoing');
        $this->modal('approve-confirmation')->close();
        $this->redirectRoute('sales.order.approval.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.sales.approval.show');
    }
}
