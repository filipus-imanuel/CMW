<?php

namespace App\Livewire\Employees\User;

use App\Models\User;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Users')]
class Index extends Component
{
    public $deleteId = null;

    #[On('cmw.employee.user.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('cmw.employee.user.delete')]
    public function confirmDelete($id): void
    {
        $this->authorize('delete user');

        $this->deleteId = $id;
        $this->modal('delete-user-confirmation')->show();
    }

    public function destroy(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete user');

        try {
            DB::transaction(function () {
                $user = User::findOrFail($this->deleteId);
                $user->update(['deleted_by' => Auth::id()]);
                $user->delete();

                Flux::toast('User deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.employee.user.refresh');
            });

            $this->deleteId = null;
            $this->modal('delete-user-confirmation')->close();
        } catch (QueryException $e) {
            Flux::toast('Cannot delete user. It may be in use.', variant: 'danger', position: 'top right');
        } catch (Exception $e) {
            Flux::toast('An error occurred while deleting the user.', variant: 'danger', position: 'top right');
        }
    }

    public function render()
    {
        return view('livewire.employees.user.index');
    }
}
