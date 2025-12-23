# Composer Component (Flux Pro)

A configurable message input with support for action buttons and rich text. Ideal for chat interfaces, AI prompts, comment sections, and messaging applications.

---

## Basic Usage

```blade
<form wire:submit="send">
    <flux:composer 
        wire:model="prompt" 
        label="Prompt" 
        label:sr-only 
        placeholder="How can I help you today?">
        
        <x-slot name="actionsLeading">
            <flux:button size="sm" variant="subtle" icon="paper-clip" />
            <flux:button size="sm" variant="subtle" icon="slash" />
            <flux:button size="sm" variant="subtle" icon="adjustments-horizontal" />
        </x-slot>
        
        <x-slot name="actionsTrailing">
            <flux:button size="sm" variant="filled" icon="microphone" />
            <flux:button type="submit" size="sm" variant="primary" icon="paper-airplane" />
        </x-slot>
    </flux:composer>
</form>
```

```php
// Livewire Component
public string $prompt = '';

public function send()
{
    $this->validate(['prompt' => 'required|min:3']);
    
    // Process message
    $this->sendMessage($this->prompt);
    
    // Clear input
    $this->prompt = '';
}
```

**Features:**
- Auto-expanding textarea
- Action buttons (leading/trailing)
- Rich text editor support
- File preview header
- Keyboard shortcuts (Cmd/Ctrl+Enter)
- Validation support
- Inline and stacked layouts

---

## With Header

Display file previews or other content above the input area.

```blade
<flux:composer 
    wire:model="prompt" 
    label="Prompt" 
    label:sr-only 
    placeholder="How can I help you today?">
    
    <x-slot name="header">
        <div class="relative border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
            <img src="https://fluxui.dev/img/demo/caleb.png" alt="Profile picture" class="size-14">
            
            <div class="absolute top-0 right-0 p-1">
                <button 
                    type="button" 
                    class="p-0.5 rounded-full bg-zinc-900/50 hover:bg-zinc-900/70 flex justify-center items-center">
                    <flux:icon icon="x-mark" variant="micro" class="text-white" />
                </button>
            </div>
        </div>
    </x-slot>
    
    <x-slot name="actionsLeading">
        <flux:button size="sm" variant="subtle" icon="paper-clip" />
    </x-slot>
    
    <x-slot name="actionsTrailing">
        <flux:button type="submit" size="sm" variant="primary" icon="paper-airplane" />
    </x-slot>
</flux:composer>
```

**Use cases:**
- Image upload preview
- File attachments
- Reply context
- Quote/reference display
- Draft attachments

---

## Inline

Place action buttons alongside the input in a single row for a compact layout.

```blade
<flux:composer 
    wire:model="prompt" 
    rows="1" 
    inline 
    label="Prompt" 
    label:sr-only 
    placeholder="How can I help you today?">
    
    <x-slot name="actionsLeading">
        <flux:button size="sm" variant="ghost" icon="plus" />
    </x-slot>
    
    <x-slot name="actionsTrailing">
        <flux:button size="sm" variant="filled" icon="microphone" />
        <flux:button type="submit" size="sm" variant="primary" icon="paper-airplane" />
    </x-slot>
</flux:composer>
```

**Benefits:**
- Compact single-row layout
- Quick message sending
- Better for narrow spaces
- Mobile-friendly

---

## Input Variant

Render the composer with border radiuses matching form inputs.

```blade
<flux:composer 
    variant="input" 
    label="Message" 
    placeholder="What's on your mind?">
    
    <x-slot name="actionsTrailing">
        <flux:button type="submit" size="sm" variant="primary">Send</flux:button>
    </x-slot>
</flux:composer>
```

**Use when:**
- Matching form aesthetics
- Integrating with other inputs
- Standard message forms
- Comment sections

---

## Height

Set the initial and maximum height using `rows` and `max-rows`.

```blade
<!-- Initial 4 rows, max 8 rows -->
<flux:composer 
    rows="4" 
    max-rows="8" 
    placeholder="Type your message...">
    <!-- Actions -->
</flux:composer>

<!-- Single line that expands -->
<flux:composer 
    rows="1" 
    max-rows="10" 
    placeholder="Type your message...">
    <!-- Actions -->
</flux:composer>
```

**Configuration tips:**
- Use `rows="1"` for chat-like interfaces
- Use `rows="3-5"` for comment sections
- Set `max-rows` to prevent excessive expansion
- Textarea auto-expands as content grows

---

## Submit Behavior

Control keyboard shortcuts for form submission.

```blade
<!-- Default: Cmd/Ctrl+Enter submits -->
<form wire:submit="send">
    <flux:composer wire:model="prompt" placeholder="Press Cmd+Enter to send...">
        <!-- Actions -->
    </flux:composer>
</form>

<!-- Enter alone submits (like chat apps) -->
<form wire:submit="send">
    <flux:composer wire:model="prompt" submit="enter" placeholder="Press Enter to send...">
        <!-- Actions -->
    </flux:composer>
</form>
```

**Options:**
- `cmd+enter` (default) - Submit with Cmd/Ctrl+Enter (allows newlines with Enter)
- `enter` - Submit with Enter alone (use Shift+Enter for newlines)

**Use `enter` for:**
- Chat interfaces
- Quick messages
- Single-line responses

**Use `cmd+enter` for:**
- Long-form content
- Multi-paragraph messages
- Comment sections

---

## Rich Text

Enable rich text formatting with the editor component.

```blade
<flux:composer 
    wire:model="prompt" 
    rows="3" 
    label="Prompt" 
    label:sr-only 
    placeholder="How can I help you today?">
    
    <x-slot name="input">
        <flux:editor 
            variant="borderless" 
            toolbar="bold italic bullet ordered | link | align" />
    </x-slot>
    
    <x-slot name="actionsLeading">
        <flux:button size="sm" variant="subtle" icon="paper-clip" />
        <flux:button size="sm" variant="subtle" icon="slash" />
        <flux:button size="sm" variant="subtle" icon="adjustments-horizontal" />
    </x-slot>
    
    <x-slot name="actionsTrailing">
        <flux:button size="sm" variant="filled" icon="microphone" />
        <flux:button type="submit" size="sm" variant="primary" icon="paper-airplane" />
    </x-slot>
</flux:composer>
```

**Features:**
- Bold, italic, underline
- Lists (bullet, ordered)
- Links
- Text alignment
- Headings
- Inline code
- And more (see Editor component docs)

---

## Disabled

Prevent user interaction with the composer.

```blade
<flux:composer disabled placeholder="Composer is disabled">
    <x-slot name="actionsTrailing">
        <flux:button type="submit" size="sm" variant="primary" disabled>Send</flux:button>
    </x-slot>
</flux:composer>
```

**Use cases:**
- During message sending
- When user lacks permission
- Form validation in progress
- Read-only mode

---

## Invalid

Automatically applies error styling when validation fails.

```blade
<flux:composer 
    wire:model="prompt" 
    label="Prompt" 
    label:sr-only 
    placeholder="Type your message...">
    
    <x-slot name="actionsTrailing">
        <flux:button type="submit" size="sm" variant="primary">Send</flux:button>
    </x-slot>
</flux:composer>

@error('prompt')
    <flux:error>{{ $message }}</flux:error>
@enderror
```

```php
// Livewire Component
public function send()
{
    $this->validate([
        'prompt' => 'required|min:3|max:1000'
    ]);
    
    // Process...
}
```

---

## API Reference

### flux:composer

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `wire:model` | string | - | Livewire property to bind to |
| `name` | string | - | Name attribute for validation |
| `placeholder` | string | - | Placeholder text when empty |
| `label` | string | - | Label text (auto-wraps in flux:field) |
| `label:sr-only` | boolean | `false` | Hide label visually (screen readers only) |
| `description` | string | - | Help text near composer |
| `description:sr-only` | boolean | `false` | Hide description visually |
| `variant` | enum | - | Variant style. Options: `input` |
| `rows` | integer | `2` | Initial number of visible text lines |
| `max-rows` | integer | - | Maximum expandable rows |
| `inline` | boolean | `false` | Display actions alongside input |
| `submit` | enum | `cmd+enter` | Submit behavior. Options: `cmd+enter`, `enter` |
| `disabled` | boolean | `false` | Prevent user interaction |
| `invalid` | boolean | `false` | Apply error styling |

**Slots:**
| Slot | Description |
|------|-------------|
| `input` | Custom input (e.g., rich text editor). Replaces default textarea |
| `header` | Content above input area (file previews, context) |
| `footer` | Content below input area |
| `actionsLeading` | Buttons at start of action bar |
| `actionsTrailing` | Buttons at end of action bar (typically submit button) |

**Attributes:**
| Attribute | Description |
|-----------|-------------|
| `data-flux-composer` | Applied to root element for styling |

---

## Usage Guidelines

**When to use Composer:**
- Chat interfaces
- AI prompt inputs
- Comment sections
- Messaging applications
- Email composition
- Note taking
- Multi-line text with actions

**When NOT to use:**
- Single-line inputs (use `flux:input`)
- Short form fields
- Search boxes
- Simple text areas without actions

**Best Practices:**
- Always provide placeholder text
- Include clear submit button
- Use appropriate `rows` for content type
- Set `max-rows` to prevent excessive height
- Add file attachment button when needed
- Use `submit="enter"` for chat-like UX
- Show character/word count for limits
- Provide visual feedback during submission
- Clear input after successful send
- Handle validation errors gracefully

---

## Common Patterns

**Chat Interface:**
```blade
<form wire:submit="sendMessage">
    <flux:composer 
        wire:model="message" 
        rows="1" 
        submit="enter"
        inline
        placeholder="Type a message...">
        
        <x-slot name="actionsLeading">
            <flux:button 
                wire:click="attachFile" 
                size="sm" 
                variant="ghost" 
                icon="paper-clip" />
        </x-slot>
        
        <x-slot name="actionsTrailing">
            <flux:button 
                type="submit" 
                size="sm" 
                variant="primary" 
                icon="paper-airplane" />
        </x-slot>
    </flux:composer>
</form>
```

**AI Prompt Input:**
```blade
<form wire:submit="generateResponse">
    <flux:composer 
        wire:model="prompt" 
        rows="3" 
        max-rows="10"
        label="Prompt" 
        label:sr-only 
        placeholder="Describe what you want to create...">
        
        <x-slot name="actionsLeading">
            <flux:button size="sm" variant="subtle" icon="photo" />
            <flux:button size="sm" variant="subtle" icon="microphone" />
            <flux:button size="sm" variant="subtle" icon="sparkles" />
        </x-slot>
        
        <x-slot name="actionsTrailing">
            <flux:text size="sm" class="text-zinc-500">
                {{ strlen($prompt) }}/2000
            </flux:text>
            <flux:button 
                type="submit" 
                size="sm" 
                variant="primary" 
                :disabled="!$prompt">
                Generate
            </flux:button>
        </x-slot>
    </flux:composer>
</form>
```

**Comment Section:**
```blade
<form wire:submit="postComment">
    <flux:composer 
        wire:model="comment" 
        variant="input"
        rows="3" 
        max-rows="8"
        label="Add a comment"
        placeholder="Share your thoughts...">
        
        <x-slot name="actionsLeading">
            <flux:button size="sm" variant="ghost" icon="face-smile" />
            <flux:button size="sm" variant="ghost" icon="photo" />
        </x-slot>
        
        <x-slot name="actionsTrailing">
            <flux:button size="sm" variant="ghost">Cancel</flux:button>
            <flux:button type="submit" size="sm" variant="primary">
                Post Comment
            </flux:button>
        </x-slot>
    </flux:composer>
</form>
```

**Rich Text Editor:**
```blade
<form wire:submit="saveDraft">
    <flux:composer 
        wire:model="content" 
        rows="10"
        label="Message Body">
        
        <x-slot name="input">
            <flux:editor 
                variant="borderless"
                toolbar="bold italic underline | bullet ordered | link image | code" />
        </x-slot>
        
        <x-slot name="actionsLeading">
            <flux:button size="sm" variant="subtle" icon="document" />
            <flux:button size="sm" variant="subtle" icon="photo" />
        </x-slot>
        
        <x-slot name="actionsTrailing">
            <flux:button wire:click="saveDraft" size="sm" variant="ghost">
                Save Draft
            </flux:button>
            <flux:button type="submit" size="sm" variant="primary">
                Send Message
            </flux:button>
        </x-slot>
    </flux:composer>
</form>
```

**With File Attachments:**
```blade
<form wire:submit="sendMessage">
    <flux:composer 
        wire:model="message" 
        placeholder="Type your message...">
        
        <x-slot name="header">
            @if($attachments)
                <div class="flex flex-wrap gap-2">
                    @foreach($attachments as $attachment)
                        <div class="relative border border-zinc-200 dark:border-zinc-700 rounded-lg p-2 flex items-center gap-2">
                            <flux:icon.document variant="mini" class="text-zinc-400" />
                            <span class="text-sm">{{ $attachment->name }}</span>
                            <button 
                                wire:click="removeAttachment({{ $loop->index }})" 
                                type="button" 
                                class="text-zinc-400 hover:text-zinc-600">
                                <flux:icon.x-mark variant="micro" />
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-slot>
        
        <x-slot name="actionsLeading">
            <flux:button 
                wire:click="$dispatch('open-file-picker')" 
                size="sm" 
                variant="subtle" 
                icon="paper-clip" />
        </x-slot>
        
        <x-slot name="actionsTrailing">
            <flux:button type="submit" size="sm" variant="primary">
                Send
            </flux:button>
        </x-slot>
    </flux:composer>
</form>
```

**Loading State:**
```blade
<form wire:submit="send">
    <flux:composer 
        wire:model="message" 
        :disabled="$sendingMessage"
        placeholder="Type your message...">
        
        <x-slot name="actionsTrailing">
            <flux:button 
                type="submit" 
                size="sm" 
                variant="primary" 
                :disabled="$sendingMessage">
                @if($sendingMessage)
                    <flux:icon.arrow-path class="animate-spin" variant="mini" />
                    Sending...
                @else
                    Send
                @endif
            </flux:button>
        </x-slot>
    </flux:composer>
</form>
```

---

## Keyboard Shortcuts

**Default (`cmd+enter`):**
- `Enter` - New line
- `Shift+Enter` - New line
- `Cmd/Ctrl+Enter` - Submit form

**Enter mode (`submit="enter"`):**
- `Enter` - Submit form
- `Shift+Enter` - New line

**All modes:**
- `Cmd/Ctrl+A` - Select all
- `Cmd/Ctrl+Z` - Undo
- `Cmd/Ctrl+Shift+Z` - Redo

---

## Validation Examples

```php
// Livewire Component
use Livewire\Attributes\Rule;

class ChatComponent extends Component
{
    #[Rule('required|min:1|max:1000')]
    public string $message = '';
    
    public function sendMessage()
    {
        $this->validate();
        
        // Send message
        Message::create([
            'user_id' => auth()->id(),
            'content' => $this->message,
        ]);
        
        // Clear input
        $this->message = '';
        
        Flux::toast('Message sent!', variant: 'success');
    }
}
```

**With character limit:**
```blade
<flux:composer wire:model="prompt" rows="3">
    <x-slot name="actionsTrailing">
        <flux:text size="sm" class="tabular-nums" :class="strlen($prompt) > 2000 ? 'text-red-600' : 'text-zinc-500'">
            {{ strlen($prompt) }}/2000
        </flux:text>
        <flux:button 
            type="submit" 
            size="sm" 
            variant="primary" 
            :disabled="strlen($prompt) > 2000 || !$prompt">
            Send
        </flux:button>
    </x-slot>
</flux:composer>
```

---

## Accessibility

- Proper label association (use `label` prop)
- Keyboard navigation fully supported
- Screen reader friendly
- Focus management
- ARIA attributes for validation
- Semantic HTML structure

---

**Reference:** https://fluxui.dev/components/composer
