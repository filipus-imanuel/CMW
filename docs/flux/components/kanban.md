# Kanban Component (Flux Pro)

A collection of cards arranged in columns, representing different stages of a workflow. Perfect for project management, task tracking, and visual workflow management.

---

## Basic Usage

```blade
<flux:kanban>
    @foreach($this->columns as $column)
        <flux:kanban.column>
            <flux:kanban.column.header :heading="$column->title" :count="count($column->cards)" />
            
            <flux:kanban.column.cards>
                @foreach($column->cards as $card)
                    <flux:kanban.card :heading="$card->title" />
                @endforeach
            </flux:kanban.column.cards>
        </flux:kanban.column>
    @endforeach
</flux:kanban>
```

```php
// Livewire Component
public function mount()
{
    $this->columns = [
        (object) [
            'title' => 'Planned',
            'cards' => [
                (object) ['title' => 'Update privacy policy in app'],
                (object) ['title' => 'Search bar suggestions broken'],
                (object) ['title' => 'Improve loading spinner visuals'],
            ]
        ],
        (object) [
            'title' => 'In Progress',
            'cards' => [
                (object) ['title' => 'Mobile responsive improvements'],
                (object) ['title' => 'Data table sorting broken'],
            ]
        ],
        (object) [
            'title' => 'In Review',
            'cards' => [
                (object) ['title' => 'Button double-click issue'],
            ]
        ],
    ];
}
```

**Features:**
- Multiple columns for workflow stages
- Cards represent individual tasks/items
- Visual organization of workflow
- Fully customizable headers and footers

---

## Column Actions

Add action buttons to column headers for managing columns and cards.

```blade
<flux:kanban.column>
    <flux:kanban.column.header :heading="$column->title" :count="count($column->cards)">
        <x-slot name="actions">
            <flux:dropdown>
                <flux:button variant="subtle" icon="ellipsis-horizontal" size="sm" />
                <flux:menu>
                    <flux:menu.item icon="plus">New card</flux:menu.item>
                    <flux:menu.item icon="archive-box">Archive column</flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item variant="danger" icon="trash">Delete</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
            
            <flux:button variant="subtle" icon="plus" size="sm" />
        </x-slot>
    </flux:kanban.column.header>
    
    <flux:kanban.column.cards>
        <!-- Cards -->
    </flux:kanban.column.cards>
</flux:kanban.column>
```

**Common actions:**
- Add new card button
- Column settings dropdown
- Archive/delete column
- Reorder columns

---

## Column Subheading

Add descriptive text below the column title using the `subheading` prop.

```blade
<flux:kanban.column>
    <flux:kanban.column.header 
        heading="Backlog" 
        subheading="Ideas and suggestions" />
    
    <flux:kanban.column.cards>
        <!-- Cards -->
    </flux:kanban.column.cards>
</flux:kanban.column>
```

**Use cases:**
- Column descriptions
- Team assignments ("Assigned to Frontend Team")
- Status indicators ("Currently blocked")
- Date ranges ("Sprint 2024-12")

---

## Column Footer

Add a footer for "New card" buttons or additional information.

```blade
<flux:kanban.column>
    <flux:kanban.column.header :heading="$column['title']" count="5" />
    
    <flux:kanban.column.cards>
        <!-- Cards -->
    </flux:kanban.column.cards>
    
    <flux:kanban.column.footer>
        <!-- Inline add card form -->
        <form>
            <flux:kanban.card>
                <div class="flex items-center gap-1">
                    <flux:heading class="flex-1">
                        <input class="w-full outline-none" placeholder="New card...">
                    </flux:heading>
                    <flux:button type="submit" variant="filled" size="sm" inset="top bottom" class="-me-1.5">
                        Add
                    </flux:button>
                </div>
            </flux:kanban.card>
        </form>
        
        <!-- Or simple button -->
        <flux:button variant="subtle" icon="plus" size="sm" align="start">
            New card
        </flux:button>
    </flux:kanban.column.footer>
</flux:kanban.column>
```

**Common patterns:**
- Quick add card button
- Inline card creation form
- Column statistics
- Load more button

---

## Card as Button

Make cards clickable by setting `as="button"` prop.

```blade
<flux:kanban.card 
    as="button" 
    wire:click="edit" 
    heading="Update privacy policy in app" />

<!-- Or with Alpine.js -->
<flux:kanban.card 
    as="button" 
    x-on:click="$dispatch('open-modal', { id: {{ $card->id }} })" 
    heading="Update privacy policy in app" />
```

**Use cases:**
- Open edit modal on click
- Navigate to detail page
- Quick actions (mark complete, assign)
- Preview card details

---

## Card Header

Add badges, tags, or metadata above the card heading.

```blade
<flux:kanban.card as="button" heading="Update privacy policy in app">
    <x-slot name="header">
        <div class="flex gap-2">
            <flux:badge color="blue" size="sm">UI</flux:badge>
            <flux:badge color="green" size="sm">Backend</flux:badge>
            <flux:badge color="red" size="sm">Bug</flux:badge>
        </div>
    </x-slot>
</flux:kanban.card>
```

**Common uses:**
- Category/type badges
- Priority indicators
- Status labels
- Tags

---

## Card Footer

Add icons, avatars, or metadata below the card heading.

```blade
<flux:kanban.card as="button" heading="Update privacy policy in app">
    <x-slot name="footer">
        <flux:icon name="bars-3-bottom-left" variant="micro" class="text-zinc-400" />
        
        <flux:avatar.group>
            <flux:avatar circle size="xs" src="https://unavatar.io/x/calebporzio" />
            <flux:avatar circle size="xs" src="https://unavatar.io/github/hugosaintemarie" />
            <flux:avatar circle size="xs" src="https://unavatar.io/github/joshhanley" />
            <flux:avatar circle size="xs">3+</flux:avatar>
        </flux:avatar.group>
    </x-slot>
</flux:kanban.card>
```

**Common uses:**
- Assignee avatars
- Comment count icon
- Attachment count
- Due date
- Progress indicators

---

## API Reference

### flux:kanban

The root kanban container.

**Slots:**
| Slot | Description |
|------|-------------|
| `default` | Multiple `flux:kanban.column` components |

---

### flux:kanban.column

Individual column in the kanban board.

**Slots:**
| Slot | Description |
|------|-------------|
| `default` | Should contain `flux:kanban.column.header`, `flux:kanban.column.cards`, and optionally `flux:kanban.column.footer` |

---

### flux:kanban.column.header

Column header with title, count, and actions.

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `heading` | string | - | Title text displayed in column header |
| `subheading` | string | - | Secondary text below heading |
| `count` | integer | - | Number displayed next to heading (typically card count) |
| `badge` | string | - | Badge text/content next to heading. Supports `badge:*` attributes |

**Slots:**
| Slot | Description |
|------|-------------|
| `default` | Custom header content (overrides heading/count if provided) |
| `actions` | Action buttons/dropdowns on right side of header |

---

### flux:kanban.column.cards

Container for cards within a column.

**Slots:**
| Slot | Description |
|------|-------------|
| `default` | Multiple `flux:kanban.card` components |

---

### flux:kanban.column.footer

Footer at bottom of column for additional actions/info.

**Slots:**
| Slot | Description |
|------|-------------|
| `default` | Custom footer content (commonly "Add card" buttons or forms) |

---

### flux:kanban.card

Individual card within a column.

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `heading` | string | - | Card title text |
| `as` | enum | `div` | Element type. Options: `button`, `div`. Use `button` for clickable cards |

**Slots:**
| Slot | Description |
|------|-------------|
| `default` | Custom card body content (overrides heading if provided) |
| `header` | Content above heading (badges, tags) |
| `footer` | Content below heading (icons, avatars, metadata) |

---

## Usage Guidelines

**When to use Kanban:**
- Project/task management workflows
- Visual status tracking (To Do → In Progress → Done)
- Sprint/iteration planning
- Sales pipeline visualization
- Content publishing workflows
- Bug tracking boards

**When NOT to use:**
- Simple lists (use tables)
- Large datasets (100+ items)
- Non-workflow data
- When drag-and-drop is critical (requires custom implementation)

**Best Practices:**
- Keep column titles short and clear
- Limit columns to 3-7 for readability
- Use card headers for quick visual scanning (badges)
- Use card footers for assignees and metadata
- Add column actions for management functions
- Use consistent badge colors across columns
- Show card counts in column headers
- Provide "Add card" buttons in footers

---

## Common Patterns

**Basic Task Board:**
```blade
<flux:kanban>
    @foreach(['To Do', 'In Progress', 'Done'] as $status)
        <flux:kanban.column>
            <flux:kanban.column.header 
                :heading="$status" 
                :count="$tasks->where('status', $status)->count()" />
            
            <flux:kanban.column.cards>
                @foreach($tasks->where('status', $status) as $task)
                    <flux:kanban.card 
                        as="button" 
                        wire:click="edit({{ $task->id }})" 
                        :heading="$task->title" />
                @endforeach
            </flux:kanban.column.cards>
            
            <flux:kanban.column.footer>
                <flux:button variant="subtle" icon="plus" size="sm" align="start">
                    New task
                </flux:button>
            </flux:kanban.column.footer>
        </flux:kanban.column>
    @endforeach
</flux:kanban>
```

**Rich Cards with Metadata:**
```blade
<flux:kanban.card as="button" wire:click="viewTask({{ $task->id }})" :heading="$task->title">
    <x-slot name="header">
        <div class="flex gap-2">
            <flux:badge :color="$task->priority_color" size="sm">{{ $task->priority }}</flux:badge>
            <flux:badge color="zinc" size="sm">{{ $task->category }}</flux:badge>
        </div>
    </x-slot>
    
    <x-slot name="footer">
        <flux:icon.calendar variant="micro" class="text-zinc-400" />
        <span class="text-xs text-zinc-500">{{ $task->due_date->format('M d') }}</span>
        
        <flux:avatar.group>
            @foreach($task->assignees as $user)
                <flux:avatar circle size="xs" :src="$user->avatar_url" />
            @endforeach
        </flux:avatar.group>
    </x-slot>
</flux:kanban.card>
```

**Column with Inline Add Form:**
```blade
<flux:kanban.column>
    <flux:kanban.column.header heading="Backlog" :count="$backlogCount">
        <x-slot name="actions">
            <flux:dropdown>
                <flux:button variant="subtle" icon="ellipsis-horizontal" size="sm" />
                <flux:menu>
                    <flux:menu.item wire:click="archiveCompleted" icon="archive-box">
                        Archive completed
                    </flux:menu.item>
                    <flux:menu.item wire:click="clearColumn" icon="trash" variant="danger">
                        Clear column
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </x-slot>
    </flux:kanban.column.header>
    
    <flux:kanban.column.cards>
        @foreach($backlogTasks as $task)
            <flux:kanban.card :heading="$task->title" />
        @endforeach
    </flux:kanban.column.cards>
    
    <flux:kanban.column.footer>
        <form wire:submit="createTask">
            <flux:kanban.card>
                <div class="flex items-center gap-1">
                    <flux:heading class="flex-1">
                        <input 
                            wire:model="newTaskTitle" 
                            class="w-full outline-none" 
                            placeholder="New task...">
                    </flux:heading>
                    <flux:button type="submit" variant="filled" size="sm" inset="top bottom">
                        Add
                    </flux:button>
                </div>
            </flux:kanban.card>
        </form>
    </flux:kanban.column.footer>
</flux:kanban.column>
```

---

## Integration Notes

**Livewire Events:**
```php
// Open edit modal when card clicked
<flux:kanban.card 
    as="button" 
    wire:click="$dispatch('edit-task', { id: {{ $task->id }} })" 
    :heading="$task->title" />

// Listen in parent component
#[On('edit-task')]
public function editTask($id)
{
    $this->task = Task::find($id);
    $this->modal('edit-task')->show();
}
```

**With Drag & Drop (Custom Implementation):**
While Flux Kanban provides the UI structure, drag-and-drop functionality requires custom implementation using Alpine.js or JavaScript libraries like Sortable.js.

---

**Reference:** https://fluxui.dev/components/kanban
