<?php

namespace App\Livewire\Masters\Currency;

use App\Models\CMW\Master\Currency;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Currencies')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.master.currency.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-currency-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete currency');

        try {
            DB::transaction(function () {
                $currency = Currency::findOrFail($this->deleteId);

                if ($currency->is_delete_locked) {
                    Flux::toast('This currency cannot be deleted.', variant: 'danger', position: 'top right');

                    return;
                }

                $currency->update(['deleted_by' => Auth::id()]);
                $currency->delete();

                Flux::toast('Currency deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.currency.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-currency-confirmation')->close();
        } catch (QueryException $e) {
            Flux::toast('Cannot delete currency. It may be in use.', variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-currency-confirmation')->close();
        } catch (Exception $e) {
            Flux::toast('An error occurred while deleting the currency.', variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-currency-confirmation')->close();
        }
    }

    public function render()
    {
        return view('livewire.masters.currency.index');
    }
}
