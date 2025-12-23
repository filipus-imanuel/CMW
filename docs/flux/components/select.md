# Flux Select Component

Choose a single option from a dropdown list. For lists of up to 5 items, consider using checkboxes or radio buttons instead.

## Basic Usage (Native)

Default variant uses browser's native `<select>` element:

```blade
<flux:select wire:model="industry" placeholder="Choose industry...">
    <flux:select.option>Photography</flux:select.option>
    <flux:select.option>Design services</flux:select.option>
    <flux:select.option>Web development</flux:select.option>
    <flux:select.option>Accounting</flux:select.option>
    <flux:select.option>Legal services</flux:select.option>
    <flux:select.option>Consulting</flux:select.option>
    <flux:select.option>Other</flux:select.option>
</flux:select>
```

## Small Size

```blade
<flux:select size="sm" placeholder="Choose industry...">
    <flux:select.option>Photography</flux:select.option>
    <flux:select.option>Design services</flux:select.option>
    <!-- ... -->
</flux:select>
```

## Custom Select - Listbox Variant (Pro)

Alternative to native select with custom styling support (icons, images):

```blade
<flux:select variant="listbox" placeholder="Choose industry...">
    <flux:select.option>Photography</flux:select.option>
    <flux:select.option>Design services</flux:select.option>
    <flux:select.option>Web development</flux:select.option>
    <flux:select.option>Accounting</flux:select.option>
    <flux:select.option>Legal services</flux:select.option>
    <flux:select.option>Consulting</flux:select.option>
    <flux:select.option>Other</flux:select.option>
</flux:select>
```

## Clearable (Pro)

Add clear button to reset selection:

```blade
<flux:select variant="listbox" clearable>
    <!-- options -->
</flux:select>
```

## Options with Icons/Images (Pro)

```blade
<flux:select variant="listbox" placeholder="Select role...">
    <flux:select.option>
        <div class="flex items-center gap-2">
            <flux:icon.shield-check variant="mini" class="text-zinc-400" />
            Owner
        </div>
    </flux:select.option>
    
    <flux:select.option>
        <div class="flex items-center gap-2">
            <flux:icon.key variant="mini" class="text-zinc-400" />
            Administrator
        </div>
    </flux:select.option>
    
    <flux:select.option>
        <div class="flex items-center gap-2">
            <flux:icon.user variant="mini" class="text-zinc-400" />
            Member
        </div>
    </flux:select.option>
    
    <flux:select.option>
        <div class="flex items-center gap-2">
            <flux:icon.eye variant="mini" class="text-zinc-400" />
            Viewer
        </div>
    </flux:select.option>
</flux:select>
```

## Searchable Select (Pro)

Makes navigating large option lists easier:

```blade
<flux:select variant="listbox" searchable placeholder="Choose industries...">
    <flux:select.option>Photography</flux:select.option>
    <flux:select.option>Design services</flux:select.option>
    <flux:select.option>Web development</flux:select.option>
    <flux:select.option>Accounting</flux:select.option>
    <flux:select.option>Legal services</flux:select.option>
    <flux:select.option>Consulting</flux:select.option>
    <flux:select.option>Other</flux:select.option>
</flux:select>
```

### Custom Search Field

```blade
<flux:select variant="listbox" searchable>
    <x-slot name="search">
        <flux:select.search class="px-4" placeholder="Search industries..." />
    </x-slot>
    <!-- options -->
</flux:select>
```

## Multiple Select (Pro)

Allow selecting multiple options:

```blade
<flux:select variant="listbox" multiple placeholder="Choose industries...">
    <flux:select.option>Photography</flux:select.option>
    <flux:select.option>Design services</flux:select.option>
    <flux:select.option>Web development</flux:select.option>
    <flux:select.option>Accounting</flux:select.option>
    <flux:select.option>Legal services</flux:select.option>
    <flux:select.option>Consulting</flux:select.option>
    <flux:select.option>Other</flux:select.option>
</flux:select>
```

### Custom Selected Suffix

```blade
<!-- Default: "2 selected" -->
<flux:select variant="listbox" selected-suffix="industries selected" multiple>
    <!-- options -->
</flux:select>

<!-- With localization -->
<flux:select variant="listbox" selected-suffix="{{ __('industries selected') }}" multiple>
    <!-- options -->
</flux:select>
```

### Checkbox Indicator

```blade
<flux:select variant="listbox" indicator="checkbox" multiple>
    <!-- options -->
</flux:select>
```

### Clear Search Behavior

```blade
<!-- Clear search on select (default) -->
<flux:select variant="listbox" searchable multiple>
    <!-- options -->
</flux:select>

<!-- Clear search only on close -->
<flux:select variant="listbox" searchable multiple clear="close">
    <!-- options -->
</flux:select>
```

## Combobox Variant (Pro)

Versatile combobox for autocomplete and complex multi-selects:

```blade
<flux:select variant="combobox" placeholder="Choose industry...">
    <flux:select.option>Photography</flux:select.option>
    <flux:select.option>Design services</flux:select.option>
    <flux:select.option>Web development</flux:select.option>
    <flux:select.option>Accounting</flux:select.option>
    <flux:select.option>Legal services</flux:select.option>
    <flux:select.option>Consulting</flux:select.option>
    <flux:select.option>Other</flux:select.option>
</flux:select>
```

### Custom Input Field

```blade
<flux:select variant="combobox">
    <x-slot name="input">
        <flux:select.input x-model="search" :invalid="$errors->has('...')" />
    </x-slot>
    <!-- options -->
</flux:select>
```

## Dynamic Options (Pro)

Server-side dynamic option generation:

```blade
<flux:select wire:model="userId" variant="combobox" :filter="false">
    <x-slot name="input">
        <flux:select.input wire:model.live="search" />
    </x-slot>
    
    @foreach ($this->users as $user)
        <flux:select.option value="{{ $user->id }}" wire:key="{{ $user->id }}">
            {{ $user->name }}
        </flux:select.option>
    @endforeach
</flux:select>
```

```php
// Livewire component
public $search = '';
public $userId = null;

#[\Livewire\Attributes\Computed]
public function users()
{
    return \App\Models\User::query()
        ->when($this->search, fn($query) => $query->where('name', 'like', '%' . $this->search . '%'))
        ->limit(20)
        ->get();
}
```

## Create Option (Pro)

Allow users to create new options using `<flux:select.option.create>`.

```blade
<flux:select wire:model="projectId" variant="combobox">
    <x-slot name="input">
        <flux:select.input wire:model="search" placeholder="Start typing..." />
    </x-slot>
    
    @foreach ($this->projects as $project)
        <flux:select.option :wire:key="$project->id">
            {{ $project->name }}
        </flux:select.option>
    @endforeach
    
    <flux:select.option.create wire:click="createProject" min-length="2">
        Create "<span wire:text="search"></span>"
    </flux:select.option.create>
</flux:select>
```

```php
// Livewire Component
public $search = '';
public $projectId = null;

public function createProject()
{
    $project = Project::create(['name' => $this->search]);
    $this->projectId = $project->id;
}
```

**Features:**
- Flux automatically hides create option when search matches existing item
- Use `min-length` prop to set minimum characters required
- Automatically disabled during requests to prevent duplicates

### With Backend Search

When using `:filter="false"`, ensure the query includes newly created items:

```blade
<flux:select wire:model="projectId" variant="combobox" :filter="false">
    <x-slot name="input">
        <flux:select.input wire:model.live="search" placeholder="Start typing..." />
    </x-slot>
    
    @foreach($this->projects as $project)
        <flux:select.option :value="$project->id">
            {{ $project->name }}
        </flux:select.option>
    @endforeach
    
    <flux:select.option.create wire:click="createProject" min-length="2">
        Create "<span wire:text="search"></span>"
    </flux:select.option.create>
</flux:select>
```

```php
#[\Livewire\Attributes\Computed]
public function projects() {
    return Project::query()
        ->where('name', 'like', '%' . trim($this->search) . '%')
        ->limit(20)->get()
        ->when(blank($this->search) && $this->projectId, function ($results) {
            return Project::query()
                ->whereIn('id', [$this->projectId])
                ->whereNotIn('id', $results->pluck('id'))
                ->get()->merge($results);
        });
}
```

**Note:** Clear search input after creation with listbox variant to prevent additional requests.

### With Validation

Validate input and reset errors on update:

```blade
<flux:select wire:model="projectId" variant="combobox" :filter="false">
    <x-slot name="input">
        <flux:select.input wire:model.live="search" placeholder="Start typing..." />
    </x-slot>
    
    @foreach($this->projects as $project)
        <flux:select.option :value="$project->id">
            {{ $project->name }}
        </flux:select.option>
    @endforeach
    
    <flux:select.option.create wire:click="createProject" min-length="2">
        Create "<span wire:text="search"></span>"
    </flux:select.option.create>
</flux:select>
```

```php
public function createProject() {
    $this->validate(['search' => 'required|unique:projects,name']);
    // Create logic...
}

public function updatedSearch() {
    $this->resetErrorBag('search');
}
```

### With Modal

Handle complex creation workflows inside a modal:

```blade
<flux:select wire:model="projectId" variant="listbox">
    @foreach($this->projects as $project)
        <flux:select.option :value="$project->id">
            {{ $project->name }}
        </flux:select.option>
    @endforeach
    
    <flux:select.option.create modal="create-project">Create new</flux:select.option>
</flux:select>

<flux:modal name="create-project" class="md:w-96">
    <form wire:submit="createProject" class="space-y-6">
        <div>
            <flux:heading size="lg">Create new project</flux:heading>
            <flux:text class="mt-2">Enter the name of the new project.</flux:text>
        </div>
        
        <flux:input wire:model="projectName" label="Name" placeholder="e.g. 'UX Research'" />
        
        <div class="flex">
            <flux:spacer />
            <flux:button type="submit" variant="primary">Create</flux:button>
        </div>
    </form>
</flux:modal>
```

## Loading Message (Pro)

Customize the loading message when create option is hidden but results aren't available:

```blade
<flux:select wire:model="projectId" variant="combobox" :filter="false">
    <!-- Options -->
    
    <x-slot name="empty">
        <flux:select.option.empty when-loading="Loading projects...">
            No projects found.
        </flux:select.option.empty>
    </x-slot>
</flux:select>
```

## Custom Button/Trigger (Pro)

Full control over the trigger element:

```blade
<flux:select variant="listbox">
    <x-slot name="button">
        <flux:select.button 
            class="rounded-full!" 
            placeholder="Choose industry..." 
            :invalid="$errors->has('...')" 
        />
    </x-slot>
    <!-- options -->
</flux:select>
```

## API Reference

### `<flux:select>`

**Props:**
- `wire:model` - Bind to Livewire property (string)
- `placeholder` - Text when no option selected (string)
- `label` - Label above select (auto-wraps in `flux:field`)
- `description` - Help text below label (when `label` provided)
- `description:trailing` - Display description below select instead of above
- `badge` - Badge text at end of label (when `label` provided)
- `size` - Size: `sm`, `xs`
- `variant` - Visual style: `default` (native), `listbox`, `combobox`
- `multiple` - Allow multiple selections (listbox only, boolean)
- `filter` - Enable client-side filtering (boolean, default: `true`)
- `searchable` - Add search input (listbox/combobox only, boolean)
- `clearable` - Show clear button (listbox/combobox only, boolean)
- `selected-suffix` - Text after selected count in multiple mode (string)
- `clear` - When to clear search: `select` (default), `close`
- `disabled` - Prevent interaction (boolean)
- `invalid` - Apply error styling (boolean)

**Slots:**
- `default` - Select options
- `trigger` - Custom trigger (typically `select.button` or `select.input`)
- `button` - Custom button for listbox variant
- `input` - Custom input for combobox variant
- `search` - Custom search field for searchable variant
- `empty` - Custom empty state (typically `select.option.empty`)

**Data Attributes:**
- `data-flux-select` - Root element identifier

### `<flux:select.option>`

**Props:**
- `value` - Value associated with option (string)
- `label` - Text content displayed for the option (string)
- `selected-label` - Text content displayed when selected (string)
- `disabled` - Prevent selecting (boolean)

**Slots:**
- `default` - Option content (can include icons, images in listbox variant)

### `<flux:select.option.create>`

Component for creating new options dynamically.

**Props:**
- `wire:click` - Livewire action to call when selected (string)
- `modal` - Name of modal to open when selected (string)
- `min-length` - Minimum characters required before showing (integer)

**Slots:**
- `default` - Create option content

### `<flux:select.option.empty>`

Component for displaying empty state and loading messages.

**Props:**
- `when-loading` - Message displayed during loading (string, default: "Loading...")

**Slots:**
- `default` - Message when no options found

### `<flux:select.button>`

Custom button for listbox variant.

**Props:**
- `placeholder` - Text when no option selected (string)
- `invalid` - Apply error styling (boolean)
- `size` - Size: `sm`, `xs`
- `disabled` - Prevent interaction (boolean)
- `clearable` - Show clear button (boolean)

### `<flux:select.input>`

Custom input for combobox variant.

**Props:**
- `placeholder` - Text when no option selected (string)
- `invalid` - Apply error styling (boolean)
- `size` - Size: `sm`, `xs`

### `<flux:select.search>`

Custom search input for searchable variant.

**Props:**
- `placeholder` - Placeholder text (string)
- `icon` - Icon name at start (string)
- `clearable` - Show clear button (boolean, default: `true`)

## Guidelines

- **When to use**:
  - Native (`default`): Simple selections, no custom styling needed
  - Listbox (`listbox`): Custom styling, icons, images required
  - Combobox (`combobox`): Autocomplete, dynamic options
- **Lists > 5 items**: Use select; for ≤ 5 items, consider radio buttons
- **Multiple selections**: Use `multiple` prop with listbox variant
- **Large lists**: Enable `searchable` for better UX
- **Dynamic data**: Use combobox with `:filter="false"` and wire:model.live
- **Icons**: Use `variant="mini"` for icons inside options
- **Validation**: Set `invalid` prop when showing errors
- **Accessibility**: Always provide meaningful placeholder text
- **Custom content**: Use slots for full control over button/input/search

**Reference:** https://fluxui.dev/components/select
