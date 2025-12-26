<?php

namespace App\Livewire\Masters\UomConversion;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('UOM Conversions')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.master.uom-conversion.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('cmw.master.uom-conversion.delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-uom-conversion-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete uom conversion');

        try {
            DB::transaction(function () {
                $conversion = \App\Models\CMW\Master\UomConversion::findOrFail($this->deleteId);
                $conversion->update(['deleted_by' => Auth::id()]);
                $conversion->delete();

                Flux::toast('UOM Conversion deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.uom-conversion.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-uom-conversion-confirmation')->close();
        } catch (\Illuminate\Database\QueryException $e) {
            Flux::toast('Cannot delete UOM conversion. It may be in use.', variant: 'danger', position: 'top right');
        } catch (\Exception $e) {
            Flux::toast('An error occurred while deleting the UOM conversion.', variant: 'danger', position: 'top right');
        }
    }

    public function render()
    {
        return view('livewire.masters.uom-conversion.index');
    }
}
