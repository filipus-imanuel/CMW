<?php

namespace App\Livewire\Masters\Position;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Positions')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.master.position.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-position-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete position');

        try {
            DB::transaction(function () {
                $position = \App\Models\CMW\Master\Position::findOrFail($this->deleteId);
                $position->update(['deleted_by' => Auth::id()]);
                $position->delete();

                Flux::toast('Position deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.position.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-position-confirmation')->close();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                Flux::toast('Cannot delete position: It is being used by other records', variant: 'danger', position: 'top right');
            } else {
                Flux::toast('Database error: '.$e->getMessage(), variant: 'danger', position: 'top right');
            }
            $this->deleteId = null;
            $this->modal('delete-position-confirmation')->close();
        } catch (\Exception $e) {
            Flux::toast('Failed to delete position: '.$e->getMessage(), variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-position-confirmation')->close();
        }
    }

    public function render()
    {
        return view('livewire.masters.position.index');
    }
}
