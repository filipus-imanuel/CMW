<?php

namespace App\Livewire\System\Setting;

use App\Models\CMW\System\Setting as SettingModel;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('System Settings')]
#[Layout('components.layouts.app')]
class Edit extends Component
{
    public $inputs = [];

    /**
     * Get settings grouped by category (from first segment of key).
     */
    #[Computed]
    public function groupedSettings()
    {
        return SettingModel::orderBy('category')
            ->orderBy('key')
            ->get()
            ->groupBy(fn ($setting) => explode('.', $setting->key)[0]);
    }

    public function mount(): void
    {
        $this->authorize('edit system setting');

        // Load current values into inputs array using nested structure
        // because wire:model="inputs.a.b.c" expects $inputs['a']['b']['c']
        $settings = SettingModel::all();
        foreach ($settings as $setting) {
            data_set($this->inputs, $setting->key, $setting->value);
        }
    }

    public function update(): void
    {
        $this->authorize('edit system setting');

        try {
            DB::transaction(function () {
                $settings = SettingModel::all();

                foreach ($settings as $setting) {
                    $newValue = data_get($this->inputs, $setting->key);

                    // Handle boolean conversion
                    if ($setting->data_type === 'boolean') {
                        $newValue = $newValue ? '1' : '0';
                    }

                    // Only update if value changed
                    if ($setting->value !== $newValue) {
                        $setting->update([
                            'value' => $newValue,
                            'updated_by' => Auth::id(),
                        ]);
                    }
                }

                Flux::toast('Settings updated successfully', variant: 'success', position: 'top right');
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Log::error('Error updating system settings: '.$e->getMessage());
            Flux::toast('Database error occurred while updating settings', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Log::error('Error updating system settings: '.$e->getMessage());
            Flux::toast('An error occurred while updating settings', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.settings.system');
    }
}
