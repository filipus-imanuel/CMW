<?php

namespace App\Livewire\Inventories\ItemPrice;

use App\Models\CMW\Master\ItemPrice;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Item Prices')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.inventories.item-price.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-item-price-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete item price');

        try {
            DB::transaction(function () {
                $itemPrice = ItemPrice::findOrFail($this->deleteId);
                $itemPrice->update(['deleted_by' => Auth::id()]);
                $itemPrice->delete();

                Flux::toast('Item Price deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.inventories.item-price.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-item-price-confirmation')->close();
        } catch (QueryException $e) {
            Flux::toast('Cannot delete item price. It may be in use.', variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-item-price-confirmation')->close();
        } catch (Exception $e) {
            Flux::toast('An error occurred while deleting the item price.', variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-item-price-confirmation')->close();
        }
    }

    public function render()
    {
        return view('livewire.inventories.item-price.index');
    }
}
