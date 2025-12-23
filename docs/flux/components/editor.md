# Editor Component (Rich Text)

A basic rich text editor built using ProseMirror and Tiptap. Supports markdown notation, accessible toolbar, and shortcut keys.

**Note**: Editor's JavaScript is loaded on-the-fly as a separate file when used (not in core Flux bundle).

## Basic Usage

```blade
<flux:editor wire:model="content" label="Description" description="Write your content here" />
```

## Toolbar Configuration

Customize toolbar items and order using space-separated list:

```blade
<flux:editor toolbar="heading | bold italic underline | align ~ undo redo" />
```

**Special characters:**
- `|` - Separator (vertical line)
- `~` - Spacer (pushes items to right)

**Available toolbar items:**
- `heading` - Heading level selector
- `bold` - Bold formatting
- `italic` - Italic formatting
- `strike` - Strikethrough
- `underline` - Underline
- `bullet` - Bulleted list
- `ordered` - Numbered list
- `blockquote` - Block quote
- `subscript` - Subscript
- `superscript` - Superscript
- `highlight` - Text highlight
- `link` - Link insertion
- `code` - Code block
- `align` - Text alignment
- `undo` - Undo action
- `redo` - Redo action

## Custom Toolbar Items

Create custom toolbar items in `resources/views/flux/editor/`:

```blade
<!-- resources/views/flux/editor/copy.blade.php -->
<flux:tooltip content="{{ __('Copy to clipboard') }}" class="contents">
    <flux:editor.button 
        x-on:click="navigator.clipboard?.writeText($el.closest('[data-flux-editor]').value); 
                    $el.setAttribute('data-copied', ''); 
                    setTimeout(() => $el.removeAttribute('data-copied'), 2000)"
    >
        <flux:icon.clipboard variant="outline" class="[[data-copied]_&]:hidden size-5!" />
        <flux:icon.clipboard-document-check variant="outline" class="hidden [[data-copied]_&]:block size-5!" />
    </flux:editor.button>
</flux:tooltip>
```

**Usage:**
```blade
<flux:editor toolbar="heading | bold italic | align ~ copy" />
```

## Custom Toolbar Layout

Compose your own toolbar with custom components:

```blade
<flux:editor>
    <flux:editor.toolbar>
        <flux:editor.heading />
        <flux:editor.separator />
        <flux:editor.bold />
        <flux:editor.italic />
        <flux:editor.strike />
        <flux:editor.separator />
        <flux:editor.bullet />
        <flux:editor.ordered />
        <flux:editor.blockquote />
        <flux:editor.separator />
        <flux:editor.link />
        <flux:editor.separator />
        <flux:editor.align />
        <flux:editor.spacer />
        
        <!-- Custom dropdown -->
        <flux:dropdown position="bottom end" offset="-15">
            <flux:editor.button icon="ellipsis-horizontal" tooltip="More" />
            <flux:menu>
                <flux:menu.item wire:click="..." icon="arrow-top-right-on-square">Preview</flux:menu.item>
                <flux:menu.item wire:click="..." icon="arrow-down-tray">Export</flux:menu.item>
                <flux:menu.item wire:click="..." icon="share">Share</flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </flux:editor.toolbar>
    
    <flux:editor.content />
</flux:editor>
```

## Height Customization

Default: min-height 200px, max-height 500px. Customize using Tailwind:

```blade
<flux:editor class="**:data-[slot=content]:min-h-[100px]!" />
```

The `[&_[data-slot=content]]:` selector targets nested content slot.

## Keyboard Shortcuts

| Action | Windows/Linux | macOS |
|--------|---------------|-------|
| Paragraph | Ctrl + Alt + 0 | Cmd + Alt + 0 |
| Heading 1 | Ctrl + Alt + 1 | Cmd + Alt + 1 |
| Heading 2 | Ctrl + Alt + 2 | Cmd + Alt + 2 |
| Heading 3 | Ctrl + Alt + 3 | Cmd + Alt + 3 |
| Bold | Ctrl + B | Cmd + B |
| Italic | Ctrl + I | Cmd + I |
| Underline | Ctrl + U | Cmd + U |
| Strikethrough | Ctrl + Shift + X | Cmd + Shift + X |
| Bullet list | Ctrl + Shift + 8 | Cmd + Shift + 8 |
| Ordered list | Ctrl + Shift + 7 | Cmd + Shift + 7 |
| Blockquote | Ctrl + Shift + B | Cmd + Shift + B |
| Code | Ctrl + E | Cmd + E |
| Highlight | Ctrl + Shift + H | Cmd + Shift + H |
| Align left | Ctrl + Shift + L | Cmd + Shift + L |
| Align center | Ctrl + Shift + E | Cmd + Shift + E |
| Align right | Ctrl + Shift + R | Cmd + Shift + R |
| Paste plain | Ctrl + Shift + V | Cmd + Shift + V |
| Line break | Ctrl + Shift + Enter | Cmd + Shift + Enter |
| Undo | Ctrl + Z | Cmd + Z |
| Redo | Ctrl + Shift + Z | Cmd + Shift + Z |

## Markdown Syntax

Type markdown syntax to trigger styling:

| Syntax | Action |
|--------|--------|
| `#` | Heading level 1 |
| `##` | Heading level 2 |
| `###` | Heading level 3 |
| `**` | Bold |
| `*` | Italic |
| `~~` | Strikethrough |
| `-` | Bullet list |
| `1.` | Ordered list |
| `>` | Blockquote |
| `` ` `` | Inline code |
| ` ``` ` | Code block |
| ` ```? ` | Code block with language class |
| `---` | Horizontal rule |

## Localization

Add translation keys to `lang/{locale}.json`:

```json
{
    "Rich text editor": "Editor de texto enriquecido",
    "Formatting": "Formato",
    "Text": "Texto",
    "Heading 1": "Encabezado 1",
    "Heading 2": "Encabezado 2",
    "Heading 3": "Encabezado 3",
    "Styles": "Estilos",
    "Bold": "Negrita",
    "Italic": "Cursiva",
    "Underline": "Subrayado",
    "Strikethrough": "Tachado",
    "Subscript": "Subíndice",
    "Superscript": "Superíndice",
    "Highlight": "Resaltar",
    "Code": "Código",
    "Bullet list": "Lista con viñetas",
    "Ordered list": "Lista numerada",
    "Blockquote": "Cita",
    "Insert link": "Insertar enlace",
    "Unlink": "Quitar enlace",
    "Align": "Alinear",
    "Left": "Izquierda",
    "Center": "Centro",
    "Right": "Derecha",
    "Undo": "Deshacer",
    "Redo": "Rehacer"
}
```

## Tiptap Extensions

**Built-in extensions:**
- Highlight
- Link
- Placeholder
- StarterKit
- Superscript
- Subscript
- TextAlign
- Underline

### Set Up Listener

Add to `<head>` in layout or `app.js`:

```javascript
document.addEventListener('flux:editor', (e) => {
    // Extension configuration
});
```

### Register Extensions

```javascript
import Youtube from 'https://cdn.jsdelivr.net/npm/@tiptap/extension-youtube@2.11.7/+esm'

document.addEventListener('flux:editor', (e) => {
    e.detail.registerExtensions([
        Youtube.configure({
            controls: false,
            nocookie: true,
        }),
    ]);
});
```

### Disable Extensions

```javascript
document.addEventListener('flux:editor', (e) => {
    e.detail.disableExtension('underline');
});
```

### Access Tiptap Instance

```javascript
document.addEventListener('flux:editor', (e) => {
    e.detail.init(({ editor }) => {
        editor.on('create', () => {});
        editor.on('update', () => {});
        editor.on('selectionUpdate', () => {});
        editor.on('transaction', () => {});
        editor.on('focus', () => {});
        editor.on('blur', () => {});
        editor.on('destroy', () => {});
        editor.on('drop', () => {});
        editor.on('paste', () => {});
        editor.on('contentError', () => {});
    });
});
```

See [Tiptap documentation](https://tiptap.dev/docs/editor/api/events) for more events.

## Properties

### flux:editor

| Property | Description |
|----------|-------------|
| `wire:model` | Bind to Livewire property |
| `value` | Initial content (when not using wire:model) |
| `label` | Label text (wraps in `flux:field`) |
| `description` | Help text below editor |
| `description:trailing` | Help text below instead of above |
| `badge` | Badge on label |
| `placeholder` | Text when editor is empty |
| `toolbar` | Space-separated list of toolbar items (`\|` separator, `~` spacer) |
| `disabled` | Prevent interaction |
| `invalid` | Error styling |

### flux:editor.toolbar

| Property | Description |
|----------|-------------|
| `items` | Space-separated list of toolbar items (alternative to `default` slot) |

### flux:editor.button

| Property | Description |
|----------|-------------|
| `icon` | Icon name |
| `iconVariant` | Icon variant: `mini` (default), `micro`, `outline` |
| `tooltip` | Tooltip text on hover |
| `disabled` | Prevent interaction |

### flux:editor.content

Container for editable content. No props (content via slot).

## Guidelines

- Editor JavaScript loads on-the-fly (separate from Flux core bundle)
- Use `toolbar` prop to customize toolbar items and order
- Use `|` for separators, `~` for spacers in toolbar
- Custom toolbar items go in `resources/views/flux/editor/`
- Markdown syntax works while typing
- All Tiptap extensions available via listener
- Default min/max height: 200px/500px (customizable via Tailwind)

**Reference**: https://fluxui.dev/components/editor
