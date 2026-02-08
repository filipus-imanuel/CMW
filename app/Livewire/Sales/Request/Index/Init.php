<?php

namespace App\Livewire\Sales\Request\Index;

use App\Models\CMW\Transaction\OrderHeader;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Sales Requests - Draft')]
class Init extends Component
{
    public $deleteId = null;

    public function mount(): void
    {
        $this->authorize('view sales request');
    }

    #[On('sales.request.refresh.init')]
    public function refresh(): void
    {
        // Triggers component refresh
    }

    #[On('sales.request.delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-sales-request-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete sales request');

        try {
            DB::transaction(function () {
                $entity = OrderHeader::init()->findOrFail($this->deleteId);
                $entity->update(['deleted_by' => Auth::id()]);
                $entity->delete();

                Flux::toast('Sales request deleted successfully', variant: 'success', position: 'top-end');
                $this->dispatch('sales.request.refresh.init');
            });

            $this->deleteId = null;
            $this->modal('delete-sales-request-confirmation')->close();
        } catch (QueryException $e) {
            Flux::toast('Cannot delete sales request. It may be in use.', variant: 'danger', position: 'top-end');
        } catch (Exception $e) {
            Flux::toast('An error occurred while deleting the sales request.', variant: 'danger', position: 'top-end');
        }
    }

    public function render()
    {
        return view('livewire.sales.request.index.init');
    }
}
