<?php

namespace App\Livewire\Masters\Company;

use App\Models\CMW\Master\Company;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Companies')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.master.company.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-company-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete company');

        try {
            DB::transaction(function () {
                $company = Company::findOrFail($this->deleteId);

                if ($company->is_delete_locked) {
                    Flux::toast('This company cannot be deleted.', variant: 'danger', position: 'top right');

                    return;
                }

                $company->update(['deleted_by' => Auth::id()]);
                $company->delete();

                Flux::toast('Company deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.company.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-company-confirmation')->close();
        } catch (QueryException $e) {
            Flux::toast('Cannot delete company. It may be in use.', variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-company-confirmation')->close();
        } catch (Exception $e) {
            Flux::toast('An error occurred while deleting the company.', variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-company-confirmation')->close();
        }
    }

    public function render()
    {
        return view('livewire.masters.company.index');
    }
}
