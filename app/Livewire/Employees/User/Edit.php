<?php

namespace App\Livewire\Employees\User;

use App\Helpers\CMW\PopulateDataHelper;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit User')]
class Edit extends Component
{
    public ?User $user = null;

    public $inputs = [];

    public $dropdown_department = [];

    public function rules(): array
    {
        return [
            'inputs.name' => 'required|string|max:255',
            'inputs.email' => 'required|email|max:255|unique:users,email,'.$this->user?->id,
            'inputs.password' => 'nullable|string|min:8|confirmed',
            'inputs.password_confirmation' => 'nullable|string|min:8',
            'inputs.department_id' => 'nullable|exists:departments,id',
            'inputs.phone' => 'nullable|string|max:20',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'inputs.name.required' => 'Name is required',
            'inputs.email.required' => 'Email is required',
            'inputs.email.email' => 'Please enter a valid email address',
            'inputs.email.unique' => 'This email is already in use',
            'inputs.password.min' => 'Password must be at least 8 characters',
            'inputs.password.confirmed' => 'Password confirmation does not match',
        ];
    }

    public function mount($id): void
    {
        $this->authorize('edit user');

        $this->user = User::findOrFail($id);

        $this->inputs = [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'password' => '',
            'password_confirmation' => '',
            'department_id' => $this->user->department_id ?? '',
            'phone' => $this->user->phone ?? '',
            'is_active' => (bool) $this->user->is_active,
        ];

        $this->dropdown_department = PopulateDataHelper::getDepartments();
    }

    public function update(): void
    {
        $this->authorize('edit user');

        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            $data = [
                'name' => $validated['inputs']['name'],
                'email' => $validated['inputs']['email'],
                'department_id' => $validated['inputs']['department_id'] ?: null,
                'phone' => $validated['inputs']['phone'] ?: null,
                'is_active' => $validated['inputs']['is_active'],
            ];

            // Only update password if provided
            if (! empty($validated['inputs']['password'])) {
                $data['password'] = $validated['inputs']['password'];
            }

            $this->user->update($data);
        });

        Flux::toast('User updated successfully', variant: 'success', position: 'top right');

        $this->redirect(route('employees.users.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.employees.user.edit');
    }
}
