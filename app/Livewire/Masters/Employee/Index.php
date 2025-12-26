<?php

namespace App\Livewire\Masters\Employee;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Employees')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.master.employee.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('cmw.master.employee.delete')]
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-employee-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete employee');

        try {
            DB::transaction(function () {
                $employee = \App\Models\CMW\Master\Employee::findOrFail($this->deleteId);
                $employee->update(['deleted_by' => Auth::id()]);
                $employee->delete();

                Flux::toast('Employee deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.employee.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-employee-confirmation')->close();
        } catch (\Illuminate\Database\QueryException $e) {
            Flux::toast('Cannot delete employee. It may be in use.', variant: 'danger', position: 'top right');
        } catch (\Exception $e) {
            Flux::toast('An error occurred while deleting the employee.', variant: 'danger', position: 'top right');
        }
    }

    public function render()
    {
        return view('livewire.masters.employee.index');
    }
}
