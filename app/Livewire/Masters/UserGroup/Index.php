<?php

namespace App\Livewire\Masters\UserGroup;

use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('User Groups')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.master.user-group.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-user-group-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete user group');

        try {
            DB::transaction(function () {
                $userGroup = \App\Models\CMW\Master\UserGroup::findOrFail($this->deleteId);
                $userGroup->update(['deleted_by' => Auth::id()]);
                $userGroup->delete();

                Flux::toast('User Group deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.user-group.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-user-group-confirmation')->close();
        } catch (QueryException $e) {
            Flux::toast('Cannot delete user group. It may be in use.', variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-user-group-confirmation')->close();
        } catch (Exception $e) {
            Flux::toast('An error occurred while deleting the user group.', variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-user-group-confirmation')->close();
        }
    }

    public function render()
    {
        return view('livewire.masters.user-group.index');
    }
}
