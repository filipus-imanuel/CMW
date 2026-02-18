<?php

namespace App\Livewire\Sales\Request\Approval;

use App\Helpers\CMW\CustomerCheckHelper;
use App\Models\CMW\Transaction\OrderHeader;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Approve Sales Request')]
class Show extends Component
{
    public ?OrderHeader $order = null;

    public $checks = [];

    public $rejection_reason = '';

    public $approvalNotes = '';

    public function mount($id): void
    {
        // Allow both view and approve permissions
        if (! Auth::user()?->can('view sales request') && ! Auth::user()?->can('approve sales request')) {
            abort(403);
        }

        $this->order = OrderHeader::with([
            'partner', 'company', 'itemCategory', 'currency',
            'details.item', 'details.itemUom.uom', 'createdBy',
        ])->findOrFail($id);

        // Status guard - only APPROVAL status can be reviewed
        if ($this->order->status !== 'APPROVAL') {
            $this->redirectRoute('sales.request.approval.index', navigate: true);

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

    public function confirmApprove(): void
    {
        $this->reset(['approvalNotes']);
        $this->resetValidation();
        $this->modal('approve-confirmation')->show();
    }

    public function processApproval(): void
    {
        $this->authorize('approve sales request');

        DB::transaction(function () {
            $updateData = [
                'status' => 'REQUEST',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => null,
                'updated_by' => Auth::id(),
            ];

            // Save approval notes if provided
            if (! empty($this->approvalNotes)) {
                $updateData['remarks'] = trim($this->order->remarks."\n\n[Approval Notes]\n".$this->approvalNotes);
            }

            $this->order->update($updateData);
        });

        Flux::toast('Sales request approved successfully', variant: 'success', position: 'top-end');
        $this->dispatch('sales.request.refresh.approval');
        $this->dispatch('sales.request.refresh.request');
        $this->modal('approve-confirmation')->close();
        $this->redirectRoute('sales.request.approval.index', navigate: true);
    }

    public function confirmReject(): void
    {
        $this->reset(['rejection_reason']);
        $this->resetValidation();
        $this->modal('reject-confirmation')->show();
    }

    public function processReject(): void
    {
        $this->authorize('reject sales request');

        $this->validate([
            'rejection_reason' => 'required|string|max:1024',
        ]);

        DB::transaction(function () {
            $this->order->update([
                'status' => 'INIT',
                'rejection_reason' => $this->rejection_reason,
                'updated_by' => Auth::id(),
            ]);
        });

        Flux::toast('Sales request rejected and returned to draft', variant: 'warning', position: 'top-end');
        $this->dispatch('sales.request.refresh.approval');
        $this->dispatch('sales.request.refresh.init');
        $this->modal('reject-confirmation')->close();
        $this->redirectRoute('sales.request.approval.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.sales.request.approval.show');
    }
}
