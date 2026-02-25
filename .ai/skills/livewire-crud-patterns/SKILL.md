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

## Sidebar Registration (Required for Every New Module)

Whenever a new module with a route is created, **always add its nav item** to `resources/views/components/layouts/app/sidebar.blade.php`.

- Wrap with `@can('view {entity}') … @endcan` if the module has a view permission.
- Place inside the correct `<flux:navlist.group>` (Master / Partners / Inventory / Sales / System).
- Use `:current="request()->routeIs('prefix.*')"` to highlight the active group.
- Keep items alphabetically ordered within the group unless business priority dictates otherwise.

```blade
{{-- Example: add inside the Inventory group --}}
@can('view stock adjustment')
<flux:navlist.item
    icon="adjustments-horizontal"
    :href="route('inventories.stock-adjustments.index')"
    :current="request()->routeIs('inventories.stock-adjustments.*')"
    wire:navigate
>{{ __('Stock Adjustments') }}</flux:navlist.item>
@endcan
```

**Checklist when creating a new module:**
1. Add routes to `routes/web.php` (use prefix group if 2+ routes share a parent)
2. Add permissions to `PermissionHelper` and re-seed
3. Add nav item to `sidebar.blade.php`
4. Add menu entry to `MenusSeeder`

## Validation Messages Pattern

Use `validationAttributes()` to give fields human-readable names, then only override `messages()` for cases the default wording cannot express (e.g. array-level constraints).

```php
// ✅ CORRECT — minimal messages() + validationAttributes()
public function rules(): array
{
    return [
        'inputs.date'              => 'required|date',
        'inputs.warehouse_id'      => 'required|exists:warehouses,id',
        'lines'                    => 'required|array|min:1',
        'lines.*.item_id'          => 'required|exists:items,id',
        'lines.*.quantity_actual'  => 'required|numeric|min:0',
    ];
}

public function messages(): array
{
    // Only override what :attribute wording cannot express
    return [
        'lines.required' => 'At least one line item is required.',
        'lines.min'      => 'At least one line item is required.',
    ];
}

public function validationAttributes(): array
{
    return [
        'inputs.date'             => 'date',
        'inputs.warehouse_id'     => 'warehouse',
        'lines'                   => 'line items',
        'lines.*.item_id'         => 'item',
        'lines.*.quantity_actual' => 'actual quantity',
    ];
}

// ❌ AVOID — per-rule message repetition
public function messages(): array
{
    return [
        'inputs.date.required'            => 'Date is required',
        'inputs.warehouse_id.required'    => 'Warehouse is required',
        'lines.*.item_id.required'        => 'Item is required',
        'lines.*.quantity_actual.required'=> 'Actual quantity is required',
        'lines.*.quantity_actual.min'     => 'Actual quantity must be at least 0',
    ];
}
```

The default Laravel message with `:attribute` is typically sufficient:
- `"The :attribute field is required."` → `"The actual quantity field is required."`
- `"The :attribute field must be at least :min."` → `"The actual quantity field must be at least 0."`

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
