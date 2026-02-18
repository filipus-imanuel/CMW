<?php

namespace App\Livewire\Inventories\Item;

use App\Models\CMW\Inventory\Item;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Items')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.inventories.item.delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-item-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete item');

        try {
            DB::transaction(function () {
                $item = Item::findOrFail($this->deleteId);
                $item->update(['deleted_by' => Auth::id()]);
                $item->delete();

                Flux::toast('Item deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.inventories.item.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-item-confirmation')->close();
        } catch (QueryException $e) {
            Flux::toast('Cannot delete item. It may be in use.', variant: 'danger', position: 'top right');
        } catch (Exception $e) {
            Flux::toast('An error occurred while deleting the item.', variant: 'danger', position: 'top right');
        }
    }

    public function render()
    {
        return view('livewire.inventories.item.index');
    }
}
