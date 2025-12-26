<?php

namespace App\Livewire\Masters\UserGroup;

use Flux\Flux;
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
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                Flux::toast('Cannot delete user group: It is being used by other records', variant: 'danger', position: 'top right');
            } else {
                Flux::toast('Database error: '.$e->getMessage(), variant: 'danger', position: 'top right');
            }
            $this->deleteId = null;
            $this->modal('delete-user-group-confirmation')->close();
        } catch (\Exception $e) {
            Flux::toast('Failed to delete user group: '.$e->getMessage(), variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-user-group-confirmation')->close();
        }
    }

    public function render()
    {
        return view('livewire.masters.user-group.index');
    }
}
