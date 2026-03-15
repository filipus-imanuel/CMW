<?php

namespace App\Livewire\Masters\PaymentMethod;

use App\Models\CMW\Master\PaymentMethod;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Payment Methods')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.master.payment-method.refresh')]
    public function refresh(): void {}

    #[On('delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-payment-method-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete payment method');

        try {
            DB::transaction(function () {
                $record = PaymentMethod::findOrFail($this->deleteId);

                if ($record->is_delete_locked) {
                    Flux::toast('This payment method cannot be deleted.', variant: 'danger', position: 'top right');

                    return;
                }

                $record->update(['deleted_by' => Auth::id()]);
                $record->delete();

                Flux::toast('Payment method deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.payment-method.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-payment-method-confirmation')->close();
        } catch (QueryException $e) {
            Flux::toast('Cannot delete payment method. It may be in use.', variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-payment-method-confirmation')->close();
        } catch (Exception $e) {
            Flux::toast('An error occurred while deleting the payment method.', variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-payment-method-confirmation')->close();
        }
    }

    public function render()
    {
        return view('livewire.masters.payment-method.index');
    }
}
