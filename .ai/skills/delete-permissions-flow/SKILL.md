# Delete Flow & Permissions

## Delete Confirmation Pattern

**Implement on all Index components**:

```php
use Flux\Flux;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\{Auth, DB};
use Exception;
use Illuminate\Database\QueryException;

class Index extends Component
{
    public $deleteId = null;
    
    #[On('module.entity.delete')]  // Or #[On('delete')] for generic
    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->modal('delete-entity-confirmation')->show();
    }
    
    public function destroy(): void
    {
        if (!$this->deleteId) {
            return;
        }
        
        $this->authorize('delete entity');
        
        try {
            DB::transaction(function () {
                $entity = Entity::findOrFail($this->deleteId);
                $entity->update(['deleted_by' => Auth::id()]);  // Audit trail
                $entity->delete();
                
                Flux::toast('Entity deleted successfully', variant: 'success', position: 'top right');
                $this->dispatch('module.entity.refresh');
            });
            
            $this->deleteId = null;
            $this->modal('delete-entity-confirmation')->close();
            
        } catch (QueryException $e) {
            Flux::toast('Cannot delete entity. It may be in use.', variant: 'danger', position: 'top right');
        } catch (Exception $e) {
            Flux::toast('An error occurred while deleting the entity.', variant: 'danger', position: 'top right');
        }
    }
}
```

## Delete Modal View

```blade
{{-- index.blade.php --}}

{{-- Delete Confirmation Modal --}}
<flux:modal name="delete-entity-confirmation">
    <flux:heading>Delete Entity</flux:heading>
    <flux:subheading>Are you sure you want to delete this entity? This action cannot be undone.</flux:subheading>

    <div class="flex gap-2 mt-6">
        <flux:spacer/>
        <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
        <flux:button variant="ghost" x-on:click="$flux.modal('delete-entity-confirmation').close()">Cancel</flux:button>
    </div>
</flux:modal>
```

**Key Pattern**: Use `x-on:click="$flux.modal('modal-name').close()"` for Cancel button.

## Key Points

- **Store `deleteId`** to track what's being deleted
- **Always wrap in `DB::transaction`**
- **Update `deleted_by`** before calling `delete()` (audit trail)
- **Catch `QueryException`** for foreign key constraint violations
- **User-friendly error messages** for constraint violations
- **Reset `deleteId`** and close modal on success

## Spatie Permission Patterns

### Permission Definition

Add to `PermissionHelper::master()`:

```php
// app/Helpers/SHP/PermissionHelper.php

public static function master(): array
{
    return [
        'masters' => [
            'position' => ['create', 'read', 'update', 'delete'],
            'currency' => ['create', 'read', 'update', 'delete'],
        ],
        'sales' => [
            'order' => ['create', 'read', 'update', 'delete', 'approve'],
        ],
        // Custom actions
        'extra' => [
            'production' => ['rollback'],  // Custom action
        ],
    ];
}
```

**Naming convention**: Singular resource + action verb

- ✅ `create sales request`
- ✅ `update sales order`
- ✅ `delete position`
- ❌ `create sales requests` (no plural)
- ❌ `position delete` (wrong order)

### Sync Permissions

After adding to `PermissionHelper::master()`:

```powershell
php artisan permission:sync
```

## Double Gate Pattern (MANDATORY)

**Always implement both UI gate and server gate**:

```blade
{{-- UI Gate: Hide button if no permission --}}
@can('update entity')
    <flux:button wire:click="edit">Edit</flux:button>
@endcan

@can('delete entity')
    <flux:button variant="danger" wire:click="confirmDelete({{ $entity->id }})">Delete</flux:button>
@endcan
```

```php
// Server Gate: Authorize before action
public function edit($id)
{
    $this->authorize('update entity');  // 403 if no permission
    // ... edit logic
}

public function destroy()
{
    $this->authorize('delete entity');  // 403 if no permission
    // ... delete logic
}
```

**Why both**: UI gate improves UX, server gate ensures security (client can't bypass).

## Permission in DataTable Columns

```php
use Illuminate\Support\Facades\Auth;

Column::make('Actions', 'id')
    ->format(fn ($value, $row) => view('components.datatables.datatable-action', [
        'rowId' => $row->id,
        'enable_this_row' => !$row->trashed(),
        'showEdit' => Auth::user()?->can('update entity'),  // Check permission
        'editHref' => route('entity.edit', ['id' => $row->id]),
        'showDelete' => Auth::user()?->can('delete entity'),  // Check permission
        'deleteDispatchEvent' => 'entity.delete',
    ])),
```

**Always use null-safe operator** (`?->`) for permission checks.
