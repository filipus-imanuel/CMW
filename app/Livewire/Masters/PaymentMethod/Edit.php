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

#[Title('Edit Payment Method')]
class Edit extends Component
{
    public ?PaymentMethod $paymentMethod = null;

    public $inputs = [
        'code' => '',
        'name' => '',
        'remarks' => '',
        'is_active' => true,
    ];

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:payment_methods,code,'.$this->paymentMethod?->id,
            'inputs.name' => 'required|string|max:100|unique:payment_methods,name,'.$this->paymentMethod?->id,
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function update(): void
    {
        $this->authorize('edit payment method');

        try {
            if ($this->paymentMethod->is_edit_locked) {
                Flux::toast('This payment method cannot be edited.', variant: 'danger', position: 'top right');

                return;
            }

            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                $this->paymentMethod->update([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'updated_by' => Auth::id(),
                ]);

                Flux::toast('Payment method updated successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.payment-method.refresh');
                $this->modal('edit-payment-method')->close();
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('Payment method with this name or code already exists', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while updating payment method', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.payment-method.edit.open')]
    public function openModal($id): void
    {
        $this->authorize('edit payment method');
        $this->resetValidation();

        $this->paymentMethod = PaymentMethod::findOrFail($id);

        $this->inputs = [
            'code' => $this->paymentMethod->code,
            'name' => $this->paymentMethod->name,
            'remarks' => $this->paymentMethod->remarks,
            'is_active' => $this->paymentMethod->is_active,
        ];

        $this->modal('edit-payment-method')->show();
    }

    public function render()
    {
        return view('livewire.masters.payment-method.edit');
    }
}
