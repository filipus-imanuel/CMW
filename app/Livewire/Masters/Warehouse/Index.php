<?php

namespace App\Livewire\Masters\Warehouse;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Warehouses')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.master.warehouse.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-warehouse-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete warehouse');

        try {
            DB::transaction(function () {
                $warehouse = \App\Models\CMW\Master\Warehouse::findOrFail($this->deleteId);
                $warehouse->update(['deleted_by' => Auth::id()]);
                $warehouse->delete();

                Flux::toast('Warehouse deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.warehouse.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-warehouse-confirmation')->close();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                Flux::toast('Cannot delete warehouse: It is being used by other records', variant: 'danger', position: 'top right');
            } else {
                Flux::toast('Database error: '.$e->getMessage(), variant: 'danger', position: 'top right');
            }
            $this->deleteId = null;
            $this->modal('delete-warehouse-confirmation')->close();
        } catch (\Exception $e) {
            Flux::toast('Failed to delete warehouse: '.$e->getMessage(), variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-warehouse-confirmation')->close();
        }
    }

    public function render()
    {
        return view('livewire.masters.warehouse.index');
    }
}
