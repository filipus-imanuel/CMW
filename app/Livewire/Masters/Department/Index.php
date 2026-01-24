<?php

namespace App\Livewire\Masters\Department;

use App\Models\CMW\Master\Department;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Departments')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.master.department.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-department-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete department');

        try {
            DB::transaction(function () {
                $department = Department::findOrFail($this->deleteId);
                $department->update(['deleted_by' => Auth::id()]);
                $department->delete();

                Flux::toast('Department deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.department.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-department-confirmation')->close();
        } catch (QueryException $e) {
            Flux::toast('Cannot delete department. It may be in use.', variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-department-confirmation')->close();
        } catch (Exception $e) {
            Flux::toast('An error occurred while deleting the department.', variant: 'danger', position: 'top right');
            $this->deleteId = null;
            $this->modal('delete-department-confirmation')->close();
        }
    }

    public function render()
    {
        return view('livewire.masters.department.index');
    }
}
