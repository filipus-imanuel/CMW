# Modal Component (Flux Pro)

Display content in a layer above the main page. Ideal for confirmations, alerts, and forms.

---

## Basic Usage

```blade
<!-- Trigger -->
<flux:modal.trigger name="edit-profile">
    <flux:button>Edit Profile</flux:button>
</flux:modal.trigger>

<!-- Modal -->
<flux:modal name="edit-profile" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Update Profile</flux:heading>
            <flux:text class="mt-2">Make changes to your personal details.</flux:text>
        </div>
        
        <flux:input label="Name" placeholder="Your name" />
        
        <div class="flex">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">Save</flux:button>
        </div>
    </div>
</flux:modal>
```

---

## Livewire Control (Recommended)

```php
// In Livewire component
public function openConfirmationModal($id)
{
    $this->selectedId = $id;
    Flux::modal('confirm-delete')->show();
}

public function confirmDelete()
{
    // Process deletion...
    
    $this->modal('confirm-delete')->close();
    Flux::toast('Deleted successfully', variant: 'success', position: 'top-end');
}

// Close all modals on the page
Flux::modals()->close();
```

```blade
<!-- Blade template -->
<flux:modal name="confirm-delete">
    <div class="space-y-6">
        <flux:heading size="lg">Confirm Delete</flux:heading>
        <flux:text>Are you sure you want to delete this item?</flux:text>
        
        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>
            <flux:button wire:click="confirmDelete" variant="danger">Delete</flux:button>
        </div>
    </div>
</flux:modal>
```

---

## Variants

### Default Modal
Standard centered modal with backdrop.

```blade
<flux:modal name="standard-modal">
    <!-- Content -->
</flux:modal>
```

### Flyout Modal
Side panel that slides from right/left/bottom.

```blade
<flux:modal name="edit-form" variant="flyout" position="right">
    <!-- Content -->
</flux:modal>

<!-- Positions: right (default), left, bottom -->
```

---

## Control Methods

**Livewire (PHP):**
```php
// Specific modal
Flux::modal('modal-name')->show();
Flux::modal('modal-name')->close();

// From component instance
$this->modal('modal-name')->show();
$this->modal('modal-name')->close();

// All modals
Flux::modals()->close();
```

**Alpine.js:**
```blade
<button x-on:click="$flux.modal('confirm').show()">Open</button>
<button x-on:click="$flux.modal('confirm').close()">Close</button>
<button x-on:click="$flux.modals().close()">Close All</button>
```

**JavaScript:**
```javascript
Flux.modal('confirm').show();
Flux.modal('confirm').close();
Flux.modals().close();
```

---

## Wire:model Binding

Bind modal state directly to Livewire property.

```blade
<flux:modal wire:model.self="showConfirmModal">
    <!-- Content -->
</flux:modal>
```

```php
public $showConfirmModal = false;

public function delete()
{
    $this->showConfirmModal = true;
}
```

**⚠️ Important:** Use `.self` modifier to prevent nested input events from interfering.

---

## Events

```blade
<!-- Close event (any close method) -->
<flux:modal @close="handleClose">
    <!-- Content -->
</flux:modal>

<!-- Cancel event (outside click or ESC key) -->
<flux:modal @cancel="handleCancel">
    <!-- Content -->
</flux:modal>
```

Alternative syntaxes: `wire:close`, `x-on:close`, `wire:cancel`, `x-on:cancel`

---

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | string | - | **Required** Unique identifier |
| `variant` | enum | `default` | `default`, `flyout`, `bare` |
| `position` | enum | `right` | Flyout position: `right`, `left`, `bottom` |
| `dismissible` | boolean | `true` | Allow close by clicking outside |
| `closable` | boolean | `true` | Show close button |
| `wire:model` | string | - | Livewire property binding |

---

## Patterns

### Confirmation Dialog
```blade
<flux:modal name="delete-confirm" class="min-w-[22rem]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Delete Project?</flux:heading>
            <flux:text class="mt-2">
                You're about to delete this project.<br>
                This action cannot be reversed.
            </flux:text>
        </div>
        
        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>
            <flux:button wire:click="confirmDelete" variant="danger">
                Delete Project
            </flux:button>
        </div>
    </div>
</flux:modal>
```

### Unique Names in Loops
```blade
@foreach($users as $user)
    <flux:modal :name="'edit-profile-'.$user->id">
        <!-- Content -->
    </flux:modal>
@endforeach
```

### Disable Outside Click
```blade
<flux:modal :dismissible="false" name="required-action">
    <!-- User must interact with modal buttons -->
</flux:modal>
```

---

## Best Practices

✅ **DO:**
- Use unique modal names (especially in loops)
- Control via Livewire methods for server-side logic
- Use `variant="ghost"` for cancel buttons
- Add `.self` modifier to `wire:model`

❌ **DON'T:**
- Use Livewire events (`$this->dispatch('open-modal')`) - use Flux methods instead
- Reuse modal names across the page
- Forget to close modals after actions complete

---

**Reference:** https://fluxui.dev/components/modal
