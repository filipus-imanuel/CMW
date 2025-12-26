<?php

namespace App\Livewire\Masters\Tax;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Taxes')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.master.tax.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-tax-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete tax');

        try {
            DB::transaction(function () {
                $tax = \App\Models\CMW\Master\Tax::findOrFail($this->deleteId);
                $tax->update(['deleted_by' => Auth::id()]);
                $tax->delete();

                Flux::toast('Tax deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.tax.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-tax-confirmation')->close();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                Flux::toast('Cannot delete tax: It is being used by other records', variant: 'danger', position: 'top right');
            } else {
                Flux::toast('Database error: '.$e->getMessage(), variant: 'danger', position: 'top right');
            }
            $this->deleteId = null;
            $this->modal('delete-tax-confirmation')->close();
        } catch (\Exception $e) {
            Flux::toast('Failed to delete tax: '.$e->getMessage(), variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-tax-confirmation')->close();
        }
    }

    public function render()
    {
        return view('livewire.masters.tax.index');
    }
}
