<?php

namespace App\Livewire\Masters\Uom;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Units of Measure')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.master.uom.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-uom-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete uom');

        try {
            DB::transaction(function () {
                $uom = \App\Models\CMW\Master\Uom::findOrFail($this->deleteId);
                $uom->update(['deleted_by' => Auth::id()]);
                $uom->delete();

                Flux::toast('Unit of Measure deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.uom.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-uom-confirmation')->close();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                Flux::toast('Cannot delete unit of measure: It is being used by other records', variant: 'danger', position: 'top right');
            } else {
                Flux::toast('Database error: '.$e->getMessage(), variant: 'danger', position: 'top right');
            }
            $this->deleteId = null;
            $this->modal('delete-uom-confirmation')->close();
        } catch (\Exception $e) {
            Flux::toast('Failed to delete unit of measure: '.$e->getMessage(), variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-uom-confirmation')->close();
        }
    }

    public function render()
    {
        return view('livewire.masters.uom.index');
    }
}
