# OTP Input Component (Flux Pro)

Capture one-time passwords with a series of individual input fields. Perfect for 2FA verification, PIN codes, license keys, and verification codes.

---

## Basic Usage

```blade
<flux:otp wire:model="code" length="6" />
```

```php
// Livewire Component
public string $code = '';
```

**Features:**
- Individual input fields for each character
- Auto-focus on next field
- Paste support (splits code across fields)
- Backspace navigation
- Keyboard shortcuts
- Auto-submit option
- Private/masked input
- Alphanumeric support

---

## Example Usage

Complete verification form with OTP input.

```blade
<flux:card>
    <form wire:submit="verify" class="space-y-8">
        <div class="max-w-64 mx-auto space-y-2">
            <flux:heading size="lg" class="text-center">Verify your account</flux:heading>
            <flux:text class="text-center">
                Please enter a one-time password from the authenticator app.
            </flux:text>
        </div>
        
        <flux:otp 
            wire:model="code" 
            length="6" 
            label="OTP Code" 
            label:sr-only 
            :error:icon="false" 
            error:class="text-center" 
            class="mx-auto" />
        
        <div class="space-y-4">
            <flux:button variant="primary" type="submit" class="w-full">Verify</flux:button>
            <flux:button wire:click="resend" class="w-full">Resend code</flux:button>
        </div>
    </form>
</flux:card>
```

```php
// Livewire Component
public string $code = '';

public function verify()
{
    $this->validate(['code' => 'required|size:6']);
    
    if (Auth::user()->verifyTwoFactorCode($this->code)) {
        session()->flash('message', 'Verification successful!');
        return redirect()->route('dashboard');
    }
    
    $this->addError('code', 'Invalid verification code.');
}

public function resend()
{
    Auth::user()->sendTwoFactorCode();
    Flux::toast('Code sent!', variant: 'success');
}
```

---

## Autosubmit

Automatically submit the form once all fields are filled.

```blade
<form wire:submit="verify" class="space-y-8">
    <div class="max-w-64 mx-auto space-y-2">
        <flux:heading size="lg" class="text-center">Verify your account</flux:heading>
        <flux:text class="text-center">
            Please enter a one-time password from the authenticator app.
        </flux:text>
    </div>
    
    <div class="space-y-6">
        <flux:otp 
            wire:model="code" 
            length="6" 
            submit="auto" 
            class="mx-auto" />
    </div>
</form>
```

**Use cases:**
- Streamlined verification flow
- Mobile-friendly UX
- Quick PIN entry
- Reduced clicks

**Note:** Form submits automatically when last character is entered.

---

## Alphanumeric

Allow non-numeric characters by setting the mode.

```blade
<flux:otp 
    wire:model="licenseKey" 
    length="10" 
    mode="alphanumeric" 
    label="License key" 
    description:trailing="Enter the license key printed on the installation disc" />
```

**Mode options:**
- `numeric` (default) - Numbers only (0-9)
- `alphanumeric` - Letters and numbers (A-Z, 0-9)
- `alpha` - Letters only (A-Z)

**Examples:**
```blade
<!-- Software license key -->
<flux:otp wire:model="license" length="16" mode="alphanumeric" label="License Key" />

<!-- Promo code -->
<flux:otp wire:model="promo" length="8" mode="alpha" label="Promo Code" />

<!-- Numeric PIN -->
<flux:otp wire:model="pin" length="4" mode="numeric" label="PIN" />
```

---

## Private

Mask sensitive input values for security.

```blade
<flux:otp wire:model="pin" length="4" private label="PIN Code" />
```

**Use for:**
- PIN codes
- Security codes
- Passwords
- Sensitive verification codes

**Display:** Shows `•` bullets instead of actual characters.

---

## Separator

Add visual separators between groups of input fields.

```blade
<flux:otp wire:model="code">
    <flux:otp.input />
    <flux:otp.input />
    <flux:otp.input />
    <flux:otp.separator />
    <flux:otp.input />
    <flux:otp.input />
    <flux:otp.input />
</flux:otp>
```

**Common patterns:**
```blade
<!-- Phone verification (3-3 split) -->
<flux:otp wire:model="code">
    <flux:otp.input />
    <flux:otp.input />
    <flux:otp.input />
    <flux:otp.separator />
    <flux:otp.input />
    <flux:otp.input />
    <flux:otp.input />
</flux:otp>

<!-- Credit card verification (4-2 split) -->
<flux:otp wire:model="cvv">
    <flux:otp.input />
    <flux:otp.input />
    <flux:otp.input />
    <flux:otp.input />
    <flux:otp.separator />
    <flux:otp.input />
    <flux:otp.input />
</flux:otp>
```

---

## Group

Group all input fields together for a unified appearance.

```blade
<flux:otp wire:model="code">
    <flux:otp.group>
        <flux:otp.input />
        <flux:otp.input />
        <flux:otp.input />
        <flux:otp.input />
        <flux:otp.input />
        <flux:otp.input />
    </flux:otp.group>
</flux:otp>
```

**Visual effect:**
- Fields appear as a single connected unit
- No gaps between inputs
- Clean, compact appearance

---

## Group Separator

Add separators between groups of input fields.

```blade
<flux:otp wire:model="code">
    <flux:otp.group>
        <flux:otp.input />
        <flux:otp.input />
        <flux:otp.input />
    </flux:otp.group>
    
    <flux:otp.separator />
    
    <flux:otp.group>
        <flux:otp.input />
        <flux:otp.input />
        <flux:otp.input />
    </flux:otp.group>
</flux:otp>
```

**Example patterns:**
```blade
<!-- License key format: XXXX-XXXX-XXXX -->
<flux:otp wire:model="license">
    <flux:otp.group>
        <flux:otp.input />
        <flux:otp.input />
        <flux:otp.input />
        <flux:otp.input />
    </flux:otp.group>
    <flux:otp.separator />
    <flux:otp.group>
        <flux:otp.input />
        <flux:otp.input />
        <flux:otp.input />
        <flux:otp.input />
    </flux:otp.group>
    <flux:otp.separator />
    <flux:otp.group>
        <flux:otp.input />
        <flux:otp.input />
        <flux:otp.input />
        <flux:otp.input />
    </flux:otp.group>
</flux:otp>
```

---

## API Reference

### flux:otp

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `wire:model` | string | - | Livewire property to bind to |
| `value` | string | - | Current value as string |
| `length` | integer | - | Number of input fields to display |
| `mode` | enum | `numeric` | Input mode. Options: `numeric`, `alphanumeric`, `alpha` |
| `private` | boolean | `false` | Mask input values with bullets |
| `submit` | enum | - | Auto-submit behavior. Options: `auto` to submit when filled |
| `autocomplete` | string | `one-time-code` | Autocomplete attribute. Use `off` to disable |
| `label` | string | - | Label text (when provided, wraps in flux:field) |
| `label:sr-only` | boolean | `false` | Hide label visually (screen readers only) |
| `description:trailing` | string | - | Help text below input |
| `error:icon` | boolean | `true` | Show error icon |
| `error:class` | string | - | CSS classes for error message |

**Slots:**
| Slot | Description |
|------|-------------|
| `default` | Custom input fields and separators. When used, `length` prop is ignored |

---

### flux:otp.input

Individual input field within OTP component.

**Props:** None

**Usage:** Place inside `flux:otp` or `flux:otp.group`.

---

### flux:otp.separator

Visual separator between input fields or groups.

**Props:** None

**Default content:** Dash (`—`)

**Customization:**
```blade
<flux:otp.separator>·</flux:otp.separator>
<flux:otp.separator>-</flux:otp.separator>
<flux:otp.separator>|</flux:otp.separator>
```

---

### flux:otp.group

Container for grouping input fields together.

**Slots:**
| Slot | Description |
|------|-------------|
| `default` | Input fields to include in the group |

---

## Usage Guidelines

**When to use OTP Input:**
- Two-factor authentication (2FA)
- SMS/email verification codes
- PIN code entry
- License key validation
- Security challenges
- Phone number verification

**When NOT to use:**
- Long codes (>10 characters - use regular input)
- Passwords (use password input)
- Non-fixed length input
- General text entry

**Best Practices:**
- Use 4-6 digits for verification codes
- Use `private` for PINs and security codes
- Set appropriate `length` (match expected code length)
- Use `submit="auto"` for seamless UX
- Provide "Resend code" option
- Show clear error messages
- Include expiration timer if applicable
- Support paste functionality (built-in)
- Use `mode="numeric"` for faster mobile keyboard

**Security considerations:**
- Always use `private` for sensitive codes
- Rate-limit verification attempts
- Expire codes after time limit
- Clear code after max failed attempts
- Use HTTPS in production

---

## Common Patterns

**2FA Verification:**
```blade
<flux:card class="max-w-md mx-auto">
    <form wire:submit="verifyTwoFactor" class="space-y-6">
        <div class="text-center space-y-2">
            <flux:heading size="lg">Two-Factor Authentication</flux:heading>
            <flux:text>Enter the 6-digit code from your authenticator app.</flux:text>
        </div>
        
        <flux:otp 
            wire:model="code" 
            length="6" 
            submit="auto"
            label="Verification Code"
            label:sr-only
            class="mx-auto" />
        
        @error('code')
            <flux:text class="text-red-600 text-center">{{ $message }}</flux:text>
        @enderror
        
        <flux:button variant="primary" type="submit" class="w-full">
            Verify
        </flux:button>
    </form>
</flux:card>
```

**SMS Verification:**
```blade
<form wire:submit="verifySMS" class="space-y-6">
    <div class="text-center">
        <flux:heading size="lg">Verify Phone Number</flux:heading>
        <flux:text>We sent a code to {{ $phone }}</flux:text>
    </div>
    
    <flux:otp 
        wire:model="code" 
        length="6" 
        submit="auto"
        label="Verification Code"
        class="mx-auto" />
    
    <div class="flex items-center justify-between">
        <flux:button wire:click="resendCode" variant="ghost" size="sm">
            Resend code
        </flux:button>
        <flux:text size="sm" class="text-zinc-500">
            Expires in {{ $expiresIn }}s
        </flux:text>
    </div>
</form>
```

**PIN Entry:**
```blade
<flux:field>
    <flux:label>Enter your PIN</flux:label>
    <flux:otp 
        wire:model="pin" 
        length="4" 
        mode="numeric"
        private 
        submit="auto" />
    <flux:error name="pin" />
</flux:field>
```

**License Key Validation:**
```blade
<flux:field>
    <flux:label>License Key</flux:label>
    <flux:description>Enter the license key from your purchase email</flux:description>
    
    <flux:otp wire:model="license" mode="alphanumeric">
        <flux:otp.group>
            <flux:otp.input />
            <flux:otp.input />
            <flux:otp.input />
            <flux:otp.input />
        </flux:otp.group>
        <flux:otp.separator />
        <flux:otp.group>
            <flux:otp.input />
            <flux:otp.input />
            <flux:otp.input />
            <flux:otp.input />
        </flux:otp.group>
        <flux:otp.separator />
        <flux:otp.group>
            <flux:otp.input />
            <flux:otp.input />
            <flux:otp.input />
            <flux:otp.input />
        </flux:otp.group>
    </flux:otp>
    
    <flux:error name="license" />
</flux:field>
```

**Backup Codes:**
```blade
<flux:card>
    <flux:heading>Use Backup Code</flux:heading>
    <flux:text class="mt-2">
        Enter one of your backup codes if you can't access your authenticator.
    </flux:text>
    
    <form wire:submit="verifyBackupCode" class="mt-6 space-y-4">
        <flux:otp 
            wire:model="backupCode" 
            length="8" 
            mode="alphanumeric"
            private />
        
        <flux:button variant="primary" type="submit" class="w-full">
            Verify Backup Code
        </flux:button>
    </form>
</flux:card>
```

---

## Keyboard Behavior

**Navigation:**
- Auto-focus next field after input
- `Backspace` - Delete and move to previous field
- `←/→` - Navigate between fields
- `Tab` - Move forward through fields
- `Shift + Tab` - Move backward through fields

**Input:**
- Paste support - automatically distributes characters across fields
- Only accepts characters based on `mode` setting
- Overwrites existing character on input

**Submission:**
- `Enter` - Submit form (unless `submit="auto"`)
- `submit="auto"` - Submits when last field is filled

---

## Validation Examples

```php
// Livewire Component
use Livewire\Attributes\Rule;

class VerifyCode extends Component
{
    #[Rule('required|size:6|numeric')]
    public string $code = '';
    
    public function verify()
    {
        $this->validate();
        
        // Verify code logic
        if (!$this->verifyCode($this->code)) {
            $this->addError('code', 'Invalid verification code. Please try again.');
            return;
        }
        
        Flux::toast('Verification successful!', variant: 'success');
        $this->redirect(route('dashboard'));
    }
}
```

**Custom validation messages:**
```php
protected function messages()
{
    return [
        'code.size' => 'The code must be exactly 6 digits.',
        'code.numeric' => 'The code must contain only numbers.',
    ];
}
```

---

## Accessibility

- Each input field has proper ARIA attributes
- Label association (use `label` prop or wrap in `flux:field`)
- Keyboard navigation fully supported
- Screen reader announcements for errors
- Focus management on paste
- High contrast mode support

---

**Reference:** https://fluxui.dev/components/otp-input
