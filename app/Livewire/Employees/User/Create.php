<?php

namespace App\Livewire\Employees\User;

use App\Helpers\CMW\PopulateDataHelper;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create User')]
class Create extends Component
{
    public $inputs = [];

    public $dropdown_department = [];

    public function rules(): array
    {
        return [
            'inputs.name' => 'required|string|max:255',
            'inputs.email' => 'required|email|max:255|unique:users,email',
            'inputs.password' => 'required|string|min:8|confirmed',
            'inputs.password_confirmation' => 'required|string|min:8',
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
            'inputs.password.required' => 'Password is required',
            'inputs.password.min' => 'Password must be at least 8 characters',
            'inputs.password.confirmed' => 'Password confirmation does not match',
            'inputs.password_confirmation.required' => 'Password confirmation is required',
        ];
    }

    public function mount(): void
    {
        $this->authorize('create user');

        $this->inputs = [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'department_id' => '',
            'phone' => '',
            'is_active' => true,
        ];

        $this->dropdown_department = PopulateDataHelper::getDepartments();
    }

    public function store(): void
    {
        $this->authorize('create user');

        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            User::create([
                'name' => $validated['inputs']['name'],
                'email' => $validated['inputs']['email'],
                'password' => $validated['inputs']['password'],
                'department_id' => $validated['inputs']['department_id'] ?: null,
                'phone' => $validated['inputs']['phone'] ?: null,
                'is_active' => $validated['inputs']['is_active'],
            ]);
        });

        Flux::toast('User created successfully', variant: 'success', position: 'top right');

        $this->redirect(route('employees.users.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.employees.user.create');
    }
}
