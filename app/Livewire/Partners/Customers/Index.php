<?php

namespace App\Livewire\Partners\Customers;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Customers')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.partners.customers.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-customer-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete customer');

        try {
            DB::transaction(function () {
                $customer = \App\Models\CMW\Master\Partner::where('is_customer', true)->findOrFail($this->deleteId);
                $customer->update(['deleted_by' => Auth::id()]);
                $customer->delete();

                Flux::toast('Customer deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.partners.customers.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-customer-confirmation')->close();
        } catch (\Illuminate\Database\QueryException $e) {
            Flux::toast('Cannot delete customer. It may be in use.', variant: 'danger', position: 'top right');
        } catch (\Exception $e) {
            Flux::toast('An error occurred while deleting the customer.', variant: 'danger', position: 'top right');
        }
    }

    public function render()
    {
        return view('livewire.partners.customers.index');
    }
}
