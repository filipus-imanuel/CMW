<?php

namespace App\Livewire\Masters\ItemCategory;

use App\Models\CMW\Master\ItemCategory;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Item Categories')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.master.item-category.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-item-category-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete item category');

        try {
            DB::transaction(function () {
                $itemCategory = ItemCategory::findOrFail($this->deleteId);

                if ($itemCategory->is_delete_locked) {
                    Flux::toast('This item category cannot be deleted.', variant: 'danger', position: 'top right');

                    return;
                }

                $itemCategory->update(['deleted_by' => Auth::id()]);
                $itemCategory->delete();

                Flux::toast('Item category deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.item-category.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-item-category-confirmation')->close();
        } catch (QueryException $e) {
            Flux::toast('Cannot delete item category. It may be in use.', variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-item-category-confirmation')->close();
        } catch (Exception $e) {
            Flux::toast('An error occurred while deleting the item category.', variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-item-category-confirmation')->close();
        }
    }

    public function render()
    {
        return view('livewire.masters.item-category.index');
    }
}
