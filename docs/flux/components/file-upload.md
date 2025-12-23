# File Upload Component

A flexible file upload component with built-in drag-and-drop support, file previews, and Livewire integration.

## Basic Usage

```blade
<flux:file-upload wire:model="photos" multiple label="Upload files">
    <flux:file-upload.dropzone
        heading="Drop files here or click to browse"
        text="JPG, PNG, GIF up to 10MB"
    />
</flux:file-upload>

<div class="mt-4 flex flex-col gap-2">
    <flux:file-item
        heading="Profile_pic.jpg"
        image="https://example.com/image.png"
        size="162400"
    >
        <x-slot name="actions">
            <flux:file-item.remove />
        </x-slot>
    </flux:file-item>
</div>
```

## Inline Layout

Compact horizontal layout:

```blade
<flux:file-upload wire:model="photos" multiple label="Upload files">
    <flux:file-upload.dropzone
        heading="Drop files or click to browse"
        text="JPG, PNG, GIF up to 10MB"
        inline
    />
</flux:file-upload>

<div class="mt-3 flex flex-col gap-2">
    <flux:file-item heading="Profile_pic.jpg">
        <x-slot name="actions">
            <flux:file-item.remove />
        </x-slot>
    </flux:file-item>
    <flux:file-item heading="Brand_banner.jpg">
        <x-slot name="actions">
            <flux:file-item.remove />
        </x-slot>
    </flux:file-item>
</div>
```

## Progress Indicator

Show upload progress (requires `text` prop):

```blade
<flux:file-upload wire:model="photos" multiple label="Upload files">
    <flux:file-upload.dropzone
        heading="Drop files or click to browse"
        text="JPG, PNG, GIF up to 10MB"
        with-progress
        inline
    />
</flux:file-upload>
```

**CSS Variables (available with `with-progress`):**
- `--flux-file-upload-progress` - Progress as decimal (e.g., "42%")
- `--flux-file-upload-progress-as-string` - Progress as quoted string (e.g., "'42%'")

## Disabled State

```blade
<flux:file-upload wire:model="photos" multiple label="Upload files" disabled>
    <flux:file-upload.dropzone
        heading="Drop files or click to browse"
        text="JPG, PNG, GIF up to 10MB"
        inline
    />
</flux:file-upload>
```

## Custom Uploader

Completely customize appearance while maintaining upload behavior:

```blade
<flux:file-upload wire:model="photo">
    <!-- Custom avatar uploader -->
    <div class="
        relative flex items-center justify-center size-20 rounded-full transition-colors cursor-pointer
        border border-zinc-200 dark:border-white/10 hover:border-zinc-300 dark:hover:border-white/10
        bg-zinc-100 hover:bg-zinc-200 dark:bg-white/10 hover:dark:bg-white/15 in-data-dragging:dark:bg-white/15
    ">
        <!-- Show uploaded file if exists -->
        @if ($photo)
            <img src="{{ $photo?->temporaryUrl() }}" class="size-full object-cover rounded-full" />
        @else
            <!-- Default icon -->
            <flux:icon name="user" variant="solid" class="text-zinc-500 dark:text-zinc-400" />
        @endif
        
        <!-- Corner upload icon -->
        <div class="absolute bottom-0 right-0 bg-white dark:bg-zinc-800 rounded-full">
            <flux:icon name="arrow-up-circle" variant="solid" class="text-zinc-500 dark:text-zinc-400" />
        </div>
    </div>
</flux:file-upload>
```

**Data attributes for custom styling:**
- `data-dragging` - Present when file is dragged over (use `in-data-dragging:` prefix)
- `data-loading` - Present while files uploading (use `in-data-loading:` prefix)

## Livewire Integration

### Single File Upload

```php
<?php
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;

class FileUpload extends Component {
    use WithFileUploads;
    
    #[Validate('image|max:10240')] // 10MB Max
    public $photo;
    
    public function removePhoto()
    {
        $this->photo->delete();
        $this->photo = null;
    }
    
    public function save()
    {
        $this->photo->store(path: 'photos');
        return $this->redirect('...');
    }
}
```

```blade
<form wire:submit="save">
    <flux:file-upload wire:model="photo" label="Upload file">
        <flux:file-upload.dropzone 
            heading="Drop file here or click to browse" 
            text="JPG, PNG, GIF up to 10MB" 
        />
    </flux:file-upload>
    
    <div class="mt-3 flex flex-col gap-2">
        @if ($photo)
            <flux:file-item
                :heading="$photo->getClientOriginalName()"
                :image="$photo->temporaryUrl()"
                :size="$photo->getSize()"
            >
                <x-slot name="actions">
                    <flux:file-item.remove 
                        wire:click="removePhoto" 
                        aria-label="{{ 'Remove file: ' . $photo->getClientOriginalName() }}" 
                    />
                </x-slot>
            </flux:file-item>
        @endif
    </div>
    
    <flux:button type="submit">Save</flux:button>
</form>
```

### Multiple Files Upload

```php
<?php
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;

class FileUpload extends Component {
    use WithFileUploads;
    
    #[Validate(['photos.*' => 'image|max:1024'])]
    public $photos = [];
    
    public function removePhoto($index)
    {
        $photo = $this->photos[$index];
        $photo->delete();
        unset($this->photos[$index]);
        $this->photos = array_values($this->photos);
    }
    
    public function save()
    {
        foreach ($this->photos as $photo) {
            $photo->store(path: 'photos');
        }
        return $this->redirect('...');
    }
}
```

```blade
<form wire:submit="save">
    <flux:file-upload wire:model="photos" label="Upload files" multiple>
        <flux:file-upload.dropzone 
            heading="Drop files here or click to browse" 
            text="JPG, PNG, GIF up to 10MB" 
        />
    </flux:file-upload>
    
    <div class="mt-3 flex flex-col gap-2">
        @foreach ($photos as $index => $photo)
            <flux:file-item
                :heading="$photo->getClientOriginalName()"
                :image="$photo->temporaryUrl()"
                :size="$photo->getSize()"
            >
                <x-slot name="actions">
                    <flux:file-item.remove 
                        wire:click="removePhoto({{ $index }})" 
                        aria-label="{{ 'Remove file: ' . $photo->getClientOriginalName() }}" 
                    />
                </x-slot>
            </flux:file-item>
        @endforeach
    </div>
    
    <flux:button type="submit">Save</flux:button>
</form>
```

## Properties

### flux:file-upload

Main container that wraps upload functionality.

| Property | Description |
|----------|-------------|
| `wire:model` | Bind to Livewire property for uploaded files |
| `name` | Input name for form submissions |
| `multiple` | Allow multiple file selection (default: false) |
| `label` | Field label text |
| `description` | Helper text below field |
| `error` | Validation error message |
| `disabled` | Prevent interaction (default: false) |

**Data Attributes:**
- `data-dragging` - Added when files dragged over (use `in-data-dragging:` prefix)
- `data-loading` - Added while uploading (use `in-data-loading:` prefix)

### flux:file-upload.dropzone

Visual drop zone area for drag-and-drop or click to browse.

| Property | Description |
|----------|-------------|
| `heading` | Main text in dropzone |
| `text` | Supporting text (file restrictions) |
| `icon` | Icon name (default: `cloud-arrow-up`) |
| `inline` | Compact horizontal layout (default: false) |
| `with-progress` | Show progress bar during upload (requires `text` prop, default: false) |

**CSS Variables (with `with-progress`):**
- `--flux-file-upload-progress` - Progress percentage (e.g., "42%")
- `--flux-file-upload-progress-as-string` - Quoted string (e.g., "'42%'")

**Data Attributes:**
- `data-dragging` - Present when files dragged over
- `data-loading` - Present while uploading

### flux:file-item

Displays uploaded file with preview, details, and actions.

| Property | Description |
|----------|-------------|
| `heading` | File name or title |
| `text` | Additional text (auto-calculated from `size` if not provided) |
| `image` | Preview image URL/path |
| `size` | File size in bytes (auto-formatted to B/KB/MB/GB) |
| `icon` | Icon when no image (default: `document`) |
| `invalid` | Display in error state (default: false) |

**Slots:**
- `actions` - Action buttons (remove, download, preview)

### flux:file-item.remove

Pre-styled remove button for file items.

| Property | Description |
|----------|-------------|
| `wire:click` | Livewire method to remove file |
| `aria-label` | Accessible label for button |

## Guidelines

- Use `WithFileUploads` trait in Livewire component
- Use `wire:model` to bind files to component property
- Add `multiple` attribute for multi-file uploads
- Use `inline` for compact horizontal layout
- Add `with-progress` to show upload progress (requires `text` prop)
- File size auto-formats (B, KB, MB, GB)
- Use `data-dragging` and `data-loading` attributes for custom styling
- Validate with Laravel validation rules
- Remember to `delete()` temporary files when removing

**Reference**: https://fluxui.dev/components/file-upload
