# Livewire CRUD Patterns

## Input Properties Pattern

**CRITICAL**: Use `$inputs[]` array, NOT individual public properties.

```php
use Livewire\Component;
use Livewire\Attributes\{Title, On};
use Illuminate\Support\Facades\{Auth, DB};
use Flux\Flux;

#[Title('Create Entity')]
class Create extends Component
{
    public $inputs = [];  // ✅ All form inputs here
    public $dropdown_data = [];  // For select options
    
    // ❌ AVOID individual properties
    // public $code;
    // public $name;
    
    public function rules()
    {
        return [
            'inputs.code' => 'required|string|max:50',
            'inputs.name' => 'required|string|max:100',
            'inputs.description' => 'nullable|string|max:500',
        ];
    }
}
```

**Blade binding**:
```blade
<flux:input wire:model="inputs.code" label="Code" />
<flux:input wire:model="inputs.name" label="Name" />
<flux:textarea wire:model="inputs.description" label="Description" />
```

## Modal Authorization Pattern

**NEVER** authorize in `mount()` for modal components included in Index pages.

```php
// ❌ WRONG - mount() runs on page load, blocks view-only users
public function mount()
{
    $this->authorize('create entity');  // 403 for view-only!
}

// ✅ CORRECT - authorize in openModal()
#[On('module.entity.create.open')]
public function openModal()
{
    $this->authorize('create entity');  // Only when modal opens
    $this->reset(['inputs']);
    $this->resetValidation();
    $this->loadDropdownData();  // Initialize here, not mount()
    $this->modal('create-entity')->show();
}

public function store()
{
    $this->authorize('create entity');  // Server-side double-check
    // ... save logic
}
```

**Why**: `mount()` runs when Index page loads. If user has only "view" permission, they get 403 before seeing the page.

**Also check**: Wrap action buttons with `@can()` in Blade views and check permissions in DataTable columns.

## Modal Initialization Pattern

**NEVER** initialize dropdowns/data in `mount()` for modal components.

```php
// ❌ WRONG - wasteful query on every page load
public function mount()
{
    $this->loadDropdownData();  // Runs even if modal never opens!
}

// ✅ CORRECT - initialize in openModal()
#[On('module.entity.create.open')]
public function openModal()
{
    $this->reset(['inputs']);
    $this->resetValidation();
    $this->loadDropdownData();  // Only when modal opens
    $this->modal('create-entity')->show();
}

private function loadDropdownData()
{
    $this->dropdown_data['customers'] = HandlingPopulateHelper::handlePopulateCustomers();
}
```

## CRUD Method Naming (Laravel RESTful)

| Component Type | Method Name | Blade Form |
|----------------|-------------|------------|
| Create | `store()` | `wire:submit="store"` |
| Edit | `update()` | `wire:submit="update"` |
| Index | `destroy()` | `wire:click="destroy"` |

**DO NOT use generic `save()` for CRUD** - transaction components (Sales, Purchase) can use `save()` for business logic.

```php
// Create component
public function store()
{
    $this->authorize('create entity');
    $validated = $this->validate();
    
    DB::transaction(function () use ($validated) {
        Entity::create([
            ...$validated['inputs'],
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);
    });
    
    Flux::toast('Created successfully', variant: 'success', position: 'top-end');
    $this->dispatch('module.entity.refresh');
    $this->modal('create-entity')->close();
}

// Edit component
public function update()
{
    $this->authorize('update entity');
    $validated = $this->validate();
    
    DB::transaction(function () use ($validated) {
        $this->model->update([
            ...$validated['inputs'],
            'updated_by' => Auth::id(),
        ]);
    });
    
    Flux::toast('Updated successfully', variant: 'success', position: 'top-end');
    $this->dispatch('module.entity.refresh');
    $this->redirectRoute('module.entity.index', navigate: true);
}
```

## Lifecycle with Status Guard

```php
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Edit Order')]
class Edit extends Component
{
    public $model;
    public $inputs = [];

    public function mount($id)
    {
        $this->authorize('update order');
        $this->model = Order::findOrFail($id);
        
        // Status guard - redirect if not editable
        if ($this->model->status != 'DRAFT') {
            $this->redirectRoute('order.show', ['id' => $id], navigate: true);
        }
        
        $this->populateInputs();
    }
    
    private function populateInputs()
    {
        $this->inputs = [
            'code' => $this->model->code,
            'name' => $this->model->name,
            // ...
        ];
    }
}
```
