<?php

namespace App\Livewire\Partners\Suppliers;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Suppliers')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.partners.suppliers.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-supplier-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete supplier');

        try {
            DB::transaction(function () {
                $supplier = \App\Models\CMW\Master\Partner::where('is_supplier', true)->findOrFail($this->deleteId);
                $supplier->update(['deleted_by' => Auth::id()]);
                $supplier->delete();

                Flux::toast('Supplier deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.partners.suppliers.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-supplier-confirmation')->close();
        } catch (\Illuminate\Database\QueryException $e) {
            Flux::toast('Cannot delete supplier. It may be in use.', variant: 'danger', position: 'top right');
        } catch (\Exception $e) {
            Flux::toast('An error occurred while deleting the supplier.', variant: 'danger', position: 'top right');
        }
    }

    public function render()
    {
        return view('livewire.partners.suppliers.index');
    }
}
