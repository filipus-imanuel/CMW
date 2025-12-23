# Pillbox Component (Flux Pro)

Multi-select component displaying selected items as removable "pills" that expand the input area dynamically.

---

## Basic Usage

```blade
<flux:pillbox wire:model="selectedTags" multiple placeholder="Choose tags...">
    <flux:pillbox.option value="design">Design</flux:pillbox.option>
    <flux:pillbox.option value="development">Development</flux:pillbox.option>
    <flux:pillbox.option value="marketing">Marketing</flux:pillbox.option>
    <flux:pillbox.option value="sales">Sales</flux:pillbox.option>
    <flux:pillbox.option value="support">Support</flux:pillbox.option>
    <flux:pillbox.option value="engineering">Engineering</flux:pillbox.option>
    <flux:pillbox.option value="product">Product</flux:pillbox.option>
    <flux:pillbox.option value="operations">Operations</flux:pillbox.option>
</flux:pillbox>
```

```php
// Livewire Component
public array $selectedTags = [];
```

**Features:**
- Selected items display as removable pills
- Click pill's × to remove
- Dropdown for adding more items
- Auto-expands as pills are added

---

## Small Size

Compact variant for tighter layouts.

```blade
<flux:pillbox size="sm" multiple placeholder="Choose tags...">
    <flux:pillbox.option value="design">Design</flux:pillbox.option>
    <flux:pillbox.option value="development">Development</flux:pillbox.option>
    <flux:pillbox.option value="marketing">Marketing</flux:pillbox.option>
</flux:pillbox>
```

---

## Searchable

Add search functionality for large option lists.

```blade
<flux:pillbox multiple searchable placeholder="Choose skills...">
    <flux:pillbox.option value="javascript">JavaScript</flux:pillbox.option>
    <flux:pillbox.option value="typescript">TypeScript</flux:pillbox.option>
    <flux:pillbox.option value="php">PHP</flux:pillbox.option>
    <flux:pillbox.option value="python">Python</flux:pillbox.option>
    <flux:pillbox.option value="ruby">Ruby</flux:pillbox.option>
    <flux:pillbox.option value="go">Go</flux:pillbox.option>
    <flux:pillbox.option value="rust">Rust</flux:pillbox.option>
    <flux:pillbox.option value="java">Java</flux:pillbox.option>
    <flux:pillbox.option value="csharp">C#</flux:pillbox.option>
    <flux:pillbox.option value="swift">Swift</flux:pillbox.option>
</flux:pillbox>
```

**Custom search placeholder:**
```blade
<flux:pillbox multiple searchable search:placeholder="Filter skills...">
    <!-- Options -->
</flux:pillbox>
```

---

## With Icons

Add visual context with icons.

```blade
<flux:pillbox multiple placeholder="Choose platforms...">
    <flux:pillbox.option value="github">
        <div class="flex items-center gap-2">
            <flux:icon.code-bracket variant="mini" class="text-zinc-400" />
            GitHub
        </div>
    </flux:pillbox.option>
    
    <flux:pillbox.option value="gitlab">
        <div class="flex items-center gap-2">
            <flux:icon.server variant="mini" class="text-zinc-400" />
            GitLab
        </div>
    </flux:pillbox.option>
    
    <flux:pillbox.option value="bitbucket">
        <div class="flex items-center gap-2">
            <flux:icon.cloud variant="mini" class="text-zinc-400" />
            Bitbucket
        </div>
    </flux:pillbox.option>
</flux:pillbox>
```

---

## With Label & Description

Use with field component pattern.

```blade
<flux:pillbox 
    wire:model="selectedCategories" 
    multiple 
    label="Categories" 
    description="Select one or more categories for this product"
    placeholder="Choose categories...">
    <flux:pillbox.option value="electronics">Electronics</flux:pillbox.option>
    <flux:pillbox.option value="clothing">Clothing</flux:pillbox.option>
    <flux:pillbox.option value="books">Books</flux:pillbox.option>
</flux:pillbox>
```

**Note:** When `label` is provided, pillbox automatically wraps itself in `flux:field`.

---

## Combobox Variant

Display an input directly within the pillbox for inline searching/filtering.

```blade
<flux:pillbox variant="combobox" multiple placeholder="Choose skills...">
    <flux:pillbox.option value="javascript">JavaScript</flux:pillbox.option>
    <flux:pillbox.option value="typescript">TypeScript</flux:pillbox.option>
    <flux:pillbox.option value="php">PHP</flux:pillbox.option>
    <flux:pillbox.option value="python">Python</flux:pillbox.option>
    <flux:pillbox.option value="ruby">Ruby</flux:pillbox.option>
    <flux:pillbox.option value="go">Go</flux:pillbox.option>
    <flux:pillbox.option value="rust">Rust</flux:pillbox.option>
    <flux:pillbox.option value="java">Java</flux:pillbox.option>
    <flux:pillbox.option value="csharp">C#</flux:pillbox.option>
    <flux:pillbox.option value="swift">Swift</flux:pillbox.option>
</flux:pillbox>
```

**Custom input with wire:model:**
```blade
<flux:pillbox wire:model="selectedTags" variant="combobox" multiple :filter="false">
    <x-slot name="input">
        <flux:pillbox.input wire:model.live="search" placeholder="Choose tags..." />
    </x-slot>
    @foreach($tags as $tag)
        <flux:pillbox.option :value="$tag->id">{{ $tag->name }}</flux:pillbox.option>
    @endforeach
</flux:pillbox>
```

---

## Create Option

Allow users to create new options using `<flux:pillbox.option.create>`.

```blade
<flux:pillbox wire:model="selectedTags" variant="combobox" multiple>
    <x-slot name="input">
        <flux:pillbox.input wire:model="search" placeholder="Choose tags..." />
    </x-slot>
    @foreach($this->tags as $tag)
        <flux:pillbox.option :value="$tag->id">{{ $tag->name }}</flux:pillbox.option>
    @endforeach
    <flux:pillbox.option.create wire:click="createTag" min-length="2">
        Create new "<span wire:text="search"></span>"
    </flux:pillbox.option.create>
</flux:pillbox>
```

```php
// Livewire Component
public $search = '';
public $selectedTags = [];

public function createTag()
{
    $tag = Tag::create(['name' => $this->search]);
    $this->selectedTags[] = $tag->id;
    $this->search = '';
}
```

**Features:**
- Flux automatically hides create option when search matches existing item
- Use `min-length` prop to set minimum characters required
- Automatically disabled during requests to prevent duplicates

**With backend search:**
```blade
<flux:pillbox wire:model.live="selectedTags" variant="combobox" multiple :filter="false">
    <x-slot name="input">
        <flux:pillbox.input wire:model.live="search" placeholder="Choose tags..." />
    </x-slot>
    @foreach($this->tags as $tag)
        <flux:pillbox.option :value="$tag->id">{{ $tag->name }}</flux:pillbox.option>
    @endforeach
    <flux:pillbox.option.create wire:click="createTag" min-length="2">
        Create "<span wire:text="search"></span>"
    </flux:pillbox.option.create>
</flux:pillbox>
```

```php
#[\Livewire\Attributes\Computed]
public function tags() {
    return \App\Models\Tag::query()
        ->where('name', 'like', '%' . trim($this->search) . '%')
        ->limit(20)->get()
        ->when(blank($this->search) && $this->selectedTags, function ($results) {
            return \App\Models\Tag::query()
                ->whereIn('id', $this->selectedTags)
                ->whereNotIn('id', $results->pluck('id'))
                ->get()->merge($results);
        });
}
```

**With validation:**
```blade
<flux:pillbox wire:model.live="selectedTags" variant="combobox" multiple :filter="false">
    <x-slot name="input">
        <flux:pillbox.input wire:model.live="search" placeholder="Choose tags..." />
    </x-slot>
    
    @foreach($this->tags as $tag)
        <flux:pillbox.option :value="$tag->id">{{ $tag->name }}</flux:pillbox.option>
    @endforeach
    
    <flux:pillbox.option.create wire:click="createTag" min-length="2">
        Create "<span wire:text="search"></span>"
    </flux:pillbox.option.create>
</flux:pillbox>
```

```php
public function createTag() {
    $this->validate(['search' => 'required|unique:tags,name']);
    // Create logic...
}

public function updatedSearch() {
    $this->resetErrorBag('search');
}
```

**With modal:**
```blade
<flux:pillbox wire:model="selectedTags" variant="combobox" placeholder="Choose tags...">
    <flux:pillbox.option.create modal="create-tag">Create new</flux:pillbox.option>
    @foreach($this->tags as $tag)
        <flux:pillbox.option :value="$tag->id">{{ $tag->name }}</flux:pillbox.option>
    @endforeach
</flux:pillbox>

<flux:modal name="create-tag" class="md:w-96">
    <form wire:submit="createTag" class="space-y-6">
        <div>
            <flux:heading size="lg">Create new tag</flux:heading>
            <flux:text class="mt-2">Enter the name of the new tag.</flux:text>
        </div>
        
        <flux:input wire:model="newTagName" label="Name" placeholder="e.g. 'Research'" />
        
        <div class="flex">
            <flux:spacer />
            <flux:button type="submit" variant="primary">Create</flux:button>
        </div>
    </form>
</flux:modal>
```

---

## Loading Message

When create option is hidden but new results aren't available yet, Flux displays "Loading..." message.

```blade
<flux:pillbox wire:model="selectedTags" variant="combobox" multiple :filter="false">
    <!-- Options -->
    
    <x-slot name="empty">
        <flux:pillbox.option.empty when-loading="Loading tags...">
            No tags found.
        </flux:pillbox.option.empty>
    </x-slot>
</flux:pillbox>
```

---

## Server-Side Filtering

Disable client-side filtering for dynamic options.

```blade
<flux:pillbox 
    wire:model="selectedUsers" 
    multiple 
    searchable 
    :filter="false"
    placeholder="Search users...">
    @foreach($users as $user)
        <flux:pillbox.option value="{{ $user->id }}">{{ $user->name }}</flux:pillbox.option>
    @endforeach
</flux:pillbox>
```

```php
// Livewire Component
public array $selectedUsers = [];
public string $search = '';

public function updatedSearch()
{
    // Fetch users based on $this->search
}
```

---

## Disabled State

```blade
<!-- Entire pillbox disabled -->
<flux:pillbox disabled multiple placeholder="Choose tags...">
    <flux:pillbox.option value="design">Design</flux:pillbox.option>
    <flux:pillbox.option value="development">Development</flux:pillbox.option>
</flux:pillbox>

<!-- Individual option disabled -->
<flux:pillbox multiple placeholder="Choose tags...">
    <flux:pillbox.option value="design">Design</flux:pillbox.option>
    <flux:pillbox.option value="premium" disabled>Premium (Locked)</flux:pillbox.option>
</flux:pillbox>
```

---

## API Reference

### flux:pillbox

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `wire:model` | string | - | Livewire property (should be array) |
| `multiple` | boolean | `false` | Enable multiple selection |
| `placeholder` | string | - | Text when no pills selected |
| `label` | string | - | Label text (auto-wraps in flux:field) |
| `description` | string | - | Help text below pillbox |
| `variant` | enum | - | `combobox` for inline input variant |
| `size` | enum | - | `sm` for compact size |
| `searchable` | boolean | `false` | Add search input (dropdown variant) |
| `search:placeholder` | string | `"Search..."` | Search input placeholder |
| `filter` | boolean | `true` | Client-side filtering (false for server-side) |
| `disabled` | boolean | `false` | Disable entire component |
| `invalid` | boolean | `false` | Error styling |

**Slots:**
- `default` - Pillbox options
- `input` - Custom input (for combobox variant)
- `trigger` - Custom trigger element
- `search` - Custom search input
- `empty` - No results message (use flux:pillbox.option.empty)

**Attributes:**
- `data-flux-pillbox` - Applied to root element

---

### flux:pillbox.option

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `value` | string | - | **Required** Option value stored in model |
| `label` | string | - | Text content displayed for the option |
| `selected-label` | string | - | Text content displayed when selected |
| `disabled` | boolean | `false` | Disable this option |
| `filterable` | boolean | `true` | Include in search filter |

**Slots:**
- `default` - Option content (text, icons, HTML)

---

### flux:pillbox.option.create

Component for allowing users to create new options.

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `wire:click` | string | - | Livewire action to call when selected |
| `modal` | string | - | Name of modal to open when selected |
| `min-length` | integer | - | Minimum characters required before showing |

**Slots:**
- `default` - Create option content

---

### flux:pillbox.option.empty

Component for displaying empty state and loading messages.

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `when-loading` | string | `"Loading..."` | Message displayed during loading |

**Slots:**
- `default` - Message when no options found

---

### flux:pillbox.input

Custom input component for combobox variant.

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `wire:model` | string | - | Livewire property for search value |
| `placeholder` | string | - | Input placeholder text |

---

### flux:pillbox.search

Custom search input for advanced use cases.

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `placeholder` | string | `"Search..."` | Search placeholder |
| `icon` | string | magnifying glass | Search icon |
| `clearable` | boolean | `true` | Show clear button |

---

### flux:pillbox.trigger

Custom trigger element.

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `placeholder` | string | - | Placeholder text |
| `invalid` | boolean | `false` | Error styling |
| `size` | enum | - | `sm` for compact |
| `clearable` | boolean | `false` | Show clear all button |

---

## Usage Guidelines

**When to use Pillbox:**
- Multiple selections (2+ items expected)
- Visual feedback for selections important
- Limited space (pills wrap dynamically)
- Tagging, categories, skills, roles
- User-created tags/options (with combobox + create option)

**When NOT to use:**
- Single selection (use `flux:select` or `flux:autocomplete`)
- Very few options (<3 - use checkboxes)
- Binary choice (use radio buttons)

**Variant Guidelines:**
- **Default (searchable):** Large option lists with dropdown search
- **Combobox:** Inline input for filtering + creating new options
- **Combobox + Create:** Tags/freeform input with suggestions

**Best Practices:**
- Use `searchable` for 10+ options (dropdown variant)
- Use `variant="combobox"` for tag creation workflows
- Add icons for visual context (platforms, tools)
- Provide clear placeholder text
- Keep option labels concise
- Use `size="sm"` in dense UIs
- Set `min-length` on create option to prevent typos

---

## Common Patterns

**Basic Tags Input:**
```blade
<flux:pillbox wire:model="tags" multiple searchable placeholder="Add tags...">
    @foreach($availableTags as $tag)
        <flux:pillbox.option value="{{ $tag->id }}">{{ $tag->name }}</flux:pillbox.option>
    @endforeach
</flux:pillbox>
```

**Tags with Create Capability:**
```blade
<flux:pillbox wire:model="selectedTags" variant="combobox" multiple>
    <x-slot name="input">
        <flux:pillbox.input wire:model="search" placeholder="Add or create tags..." />
    </x-slot>
    @foreach($this->tags as $tag)
        <flux:pillbox.option :value="$tag->id">{{ $tag->name }}</flux:pillbox.option>
    @endforeach
    <flux:pillbox.option.create wire:click="createTag" min-length="2">
        Create new "<span wire:text="search"></span>"
    </flux:pillbox.option.create>
</flux:pillbox>
```

**Role Assignment:**
```blade
<flux:pillbox wire:model="userRoles" multiple label="User Roles" placeholder="Select roles...">
    <flux:pillbox.option value="admin">Administrator</flux:pillbox.option>
    <flux:pillbox.option value="editor">Editor</flux:pillbox.option>
    <flux:pillbox.option value="viewer">Viewer</flux:pillbox.option>
</flux:pillbox>
```

**Category Selection with Icons:**
```blade
<flux:pillbox wire:model="categories" multiple placeholder="Categories...">
    @foreach($categories as $category)
        <flux:pillbox.option value="{{ $category->id }}">
            <div class="flex items-center gap-2">
                <flux:icon.{{ $category->icon }} variant="mini" class="text-zinc-400" />
                {{ $category->name }}
            </div>
        </flux:pillbox.option>
    @endforeach
</flux:pillbox>
```

---

**Reference:** https://fluxui.dev/components/pillbox
