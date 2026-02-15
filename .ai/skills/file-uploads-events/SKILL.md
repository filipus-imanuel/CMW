# File Uploads & Events

## File Upload Pattern

**CRITICAL**: Always add `WithFileUploads` trait when component handles uploads.

```php
use Livewire\Component;
use Livewire\WithFileUploads;  // ⚠️ REQUIRED
use App\Helpers\FileUploadHelper;

class Create extends Component
{
    use WithFileUploads;  // ⚠️ Don't forget this!
    
    public $photo_file;
    
    public function rules()
    {
        return [
            'photo_file' => 'nullable|file|mimes:jpg,png,pdf|max:10240',  // 10MB max
        ];
    }
    
    public function store()
    {
        $validated = $this->validate();
        
        $photoPath = null;
        if ($this->photo_file) {
            $photoPath = FileUploadHelper::uploadFile(
                $this->photo_file, 
                'Sales/Costs'  // S3 folder path
            );
        }
        
        Entity::create([
            'photo_path' => $photoPath,
            // ... other fields
        ]);
    }
}
```

**Why**: Without `WithFileUploads` trait, Livewire can't handle file inputs.

## Blade File Input

```blade
<flux:file-upload 
    wire:model="photo_file" 
    label="Upload Photo"
    accept="image/jpeg,image/png,application/pdf"
/>

@error('photo_file')
    <flux:error>{{ $message }}</flux:error>
@enderror
```

## File Validation Rules

```php
// Image only
'photo' => 'required|image|mimes:jpg,png|max:10240',  // 10MB

// Documents
'document' => 'required|file|mimes:pdf,doc,docx|max:10240',  // 10MB

// Multiple files
'attachments.*' => 'file|mimes:jpg,png,pdf|max:10240',  // 10MB each
```

**Default max size**: 10MB (10240 KB) - adjust if needed for specific use cases.

## Toast Notifications

```php
use Flux\Flux;

// Success
Flux::toast('Saved successfully', variant: 'success', position: 'top-end');

// Error
Flux::toast('An error occurred', variant: 'danger', position: 'top-end');

// Warning
Flux::toast('Please review the data', variant: 'warning', position: 'top-end');

// Info
Flux::toast('Processing in background', variant: 'info', position: 'top-end');
```

**Variants**: `success`, `danger`, `warning`, `info`

**Positions**: `top-start`, `top-end`, `bottom-start`, `bottom-end`, `top-center`, `bottom-center`

## Event Dispatching

**Convention**: `shp.{module}.{entity}.{action}`

```php
// Dispatch event after state change
public function store()
{
    DB::transaction(function () {
        // ... create entity
    });
    
    Flux::toast('Created successfully', variant: 'success', position: 'top-end');
    
    // Notify other components to refresh
    $this->dispatch('shp.sales.order.refresh');
    
    $this->modal('create-order')->close();
}
```

### Event Listeners

```php
use Livewire\Attributes\On;

#[On('shp.sales.order.refresh')]
public function refreshData()
{
    $this->model = $this->model->fresh(['partner', 'details']);
    $this->populateInputs();
}
```

### Complex Event Examples

```php
// Header + detail refresh
$this->dispatch('shp.sales.request.edit.refresh_order_detail');

// Billing page refresh after payment
$this->dispatch('shp.sales.billing.show.refresh');

// Production intermediates reload
$this->dispatch('shp.production.refresh_intermediates');
```

## Component Refresh Pattern

**Pattern**: Mutation → `fresh()` → dispatch event → listener `fresh()` + repopulate

```php
public function update()
{
    DB::transaction(function () {
        $this->model->update([
            'name' => $this->inputs['name'],
            'updated_by' => Auth::id(),
        ]);
        
        TransactionHelper::updatePurchaseOrderHeaderTotals($this->model);
    });
    
    // Refresh with relationships
    $this->model = $this->model->fresh(['partner', 'details', 'costs']);
    
    // Notify other components
    $this->dispatch('shp.sales.order.refresh');
    
    Flux::toast('Updated successfully', variant: 'success', position: 'top-end');
}
```

**Why `fresh()`**: Ensures model reflects DB state after transaction, especially calculated fields.

## FileUploadHelper Usage

```php
use App\Helpers\FileUploadHelper;

// Upload single file
$path = FileUploadHelper::uploadFile($file, 'Sales/Costs');

// Upload to DigitalOcean Spaces (S3)
// Returns: storage path (e.g., 'Sales/Costs/2025/02/filename.jpg')

// Delete file
if ($entity->photo_path) {
    FileUploadHelper::deleteFile($entity->photo_path);
}

// Get public URL
$url = FileUploadHelper::getUrl($entity->photo_path);
```

**Storage**: Files uploaded to DigitalOcean Spaces (S3-compatible).

## Common Gotcha

**Forgot `WithFileUploads` trait**: File upload field doesn't work, no error shown. Always add the trait!
