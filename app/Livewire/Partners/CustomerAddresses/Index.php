<?php

namespace App\Livewire\Partners\CustomerAddresses;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Customer Addresses')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.partners.customer-addresses.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-customer-address-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete partner address');

        try {
            DB::transaction(function () {
                $address = \App\Models\CMW\Master\PartnerAddress::findOrFail($this->deleteId);
                $address->update(['deleted_by' => Auth::id()]);
                $address->delete();

                Flux::toast('Customer Address deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.partners.customer-addresses.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-customer-address-confirmation')->close();
        } catch (\Illuminate\Database\QueryException $e) {
            Flux::toast('Cannot delete customer address. It may be in use.', variant: 'danger', position: 'top right');
        } catch (\Exception $e) {
            Flux::toast('An error occurred while deleting the customer address.', variant: 'danger', position: 'top right');
        }
    }

    public function render()
    {
        return view('livewire.partners.customer-addresses.index');
    }
}
