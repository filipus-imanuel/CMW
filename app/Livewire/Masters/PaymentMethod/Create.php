<?php

namespace App\Livewire\Masters\PaymentMethod;

use App\Models\CMW\Master\PaymentMethod;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Payment Method')]
class Create extends Component
{
    public $inputs = [
        'code' => '',
        'name' => '',
        'remarks' => '',
        'is_active' => true,
    ];

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:payment_methods,code',
            'inputs.name' => 'required|string|max:100|unique:payment_methods,name',
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function store(): void
    {
        $this->authorize('create payment method');

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                PaymentMethod::create([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'created_by' => Auth::id(),
                ]);

                Flux::toast('Payment method created successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.payment-method.refresh');
                $this->modal('create-payment-method')->close();
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('Payment method with this name or code already exists', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while creating payment method', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.payment-method.create.open')]
    public function openModal(): void
    {
        $this->authorize('create payment method');

        $this->inputs = [
            'code' => '',
            'name' => '',
            'remarks' => '',
            'is_active' => true,
        ];

        $this->resetValidation();
        $this->modal('create-payment-method')->show();
    }

    public function render()
    {
        return view('livewire.masters.payment-method.create');
    }
}
