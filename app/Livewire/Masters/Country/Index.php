<?php

namespace App\Livewire\Masters\Country;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Countries')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.master.country.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-country-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete country');

        try {
            DB::transaction(function () {
                $country = \App\Models\CMW\Master\Country::findOrFail($this->deleteId);
                $country->update(['deleted_by' => Auth::id()]);
                $country->delete();

                Flux::toast('Country deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.country.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-country-confirmation')->close();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                Flux::toast('Cannot delete country: It is being used by other records', variant: 'danger', position: 'top right');
            } else {
                Flux::toast('Database error: '.$e->getMessage(), variant: 'danger', position: 'top right');
            }
            $this->deleteId = null;
            $this->modal('delete-country-confirmation')->close();
        } catch (\Exception $e) {
            Flux::toast('Failed to delete country: '.$e->getMessage(), variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-country-confirmation')->close();
        }
    }

    public function render()
    {
        return view('livewire.masters.country.index');
    }
}
