<?php

namespace App\Livewire\Masters\ExchangeRate;

use App\Models\CMW\Master\ExchangeRate;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Exchange Rates')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.master.exchange-rate.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-exchange-rate-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete exchange rate');

        try {
            DB::transaction(function () {
                $exchangeRate = ExchangeRate::findOrFail($this->deleteId);

                $exchangeRate->update(['deleted_by' => Auth::id()]);
                $exchangeRate->delete();

                Flux::toast('Exchange rate deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.exchange-rate.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-exchange-rate-confirmation')->close();
        } catch (QueryException $e) {
            Flux::toast('Cannot delete exchange rate. It may be in use.', variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-exchange-rate-confirmation')->close();
        } catch (Exception $e) {
            Flux::toast('An error occurred while deleting the exchange rate.', variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-exchange-rate-confirmation')->close();
        }
    }

    public function render()
    {
        return view('livewire.masters.exchange-rate.index');
    }
}
