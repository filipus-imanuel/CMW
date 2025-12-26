<?php

namespace App\Livewire\Masters\CreditTerm;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Credit Terms')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.master.credit-term.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('cmw.master.credit-term.delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-credit-term-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete credit term');

        try {
            DB::transaction(function () {
                $creditTerm = \App\Models\CMW\Master\CreditTerm::findOrFail($this->deleteId);
                $creditTerm->update(['deleted_by' => Auth::id()]);
                $creditTerm->delete();

                Flux::toast('Credit Term deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.credit-term.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-credit-term-confirmation')->close();
        } catch (\Illuminate\Database\QueryException $e) {
            Flux::toast('Cannot delete credit term. It may be in use.', variant: 'danger', position: 'top right');
        } catch (\Exception $e) {
            Flux::toast('An error occurred while deleting the credit term.', variant: 'danger', position: 'top right');
        }
    }

    public function render()
    {
        return view('livewire.masters.credit-term.index');
    }
}
