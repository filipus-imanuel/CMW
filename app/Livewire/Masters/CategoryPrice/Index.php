<?php

namespace App\Livewire\Masters\CategoryPrice;

use App\Models\CMW\Master\CategoryPrice;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Category Prices')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.master.category-price.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-category-price-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete category price');

        try {
            DB::transaction(function () {
                $categoryPrice = CategoryPrice::findOrFail($this->deleteId);
                $categoryPrice->update(['deleted_by' => Auth::id()]);
                $categoryPrice->delete();

                Flux::toast('Category Price deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.category-price.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-category-price-confirmation')->close();
        } catch (QueryException $e) {
            Flux::toast('Cannot delete category price. It may be in use.', variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-category-price-confirmation')->close();
        } catch (Exception $e) {
            Flux::toast('An error occurred while deleting the category price.', variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-category-price-confirmation')->close();
        }
    }

    public function render()
    {
        return view('livewire.masters.category-price.index');
    }
}
