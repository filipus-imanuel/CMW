<?php

namespace App\Livewire\Partners\SupplierAddresses;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Supplier Addresses')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.partners.supplier-addresses.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-supplier-address-confirmation')->show();
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

                Flux::toast('Supplier Address deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.partners.supplier-addresses.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-supplier-address-confirmation')->close();
        } catch (\Illuminate\Database\QueryException $e) {
            Flux::toast('Cannot delete supplier address. It may be in use.', variant: 'danger', position: 'top right');
        } catch (\Exception $e) {
            Flux::toast('An error occurred while deleting the supplier address.', variant: 'danger', position: 'top right');
        }
    }

    public function render()
    {
        return view('livewire.partners.supplier-addresses.index');
    }
}
