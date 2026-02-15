# Repository Constraints

## Tech Stack (Fixed)

- **Backend**: Laravel 12, MySQL, Spatie Permissions
- **Frontend**: Livewire 3, Flux Pro (UI), Volt (SFC), Vite
- **Storage**: DigitalOcean Spaces (S3-compatible)
- **Testing**: Pest 4, PHPUnit 12
- **Code Quality**: Laravel Pint

## Structure Immutability

**DO NOT** create new base folders without approval:
- Existing: `app/`, `resources/`, `routes/`, `database/`, `docs/`, `temp_debug/`, `.ai/`
- New folders require explicit user approval

**DO NOT** change dependencies without approval:
- No `composer require` or `npm install` without user confirmation
- Keep existing package versions unless explicitly requested

## Development Commands

```powershell
# First-time setup
composer install
php artisan migrate
php artisan permission:sync

# Daily development (concurrent: serve + queue + vite)
composer run dev
```

## Documentation Policy

**Only create documentation files when explicitly requested by user.**

Do not auto-generate:
- README updates
- Change logs
- Architecture docs
- Unless user specifically asks for documentation

## Directory Conventions

```
app/
  Helpers/              # Autoloaded utilities (TransactionHelper, FileUploadHelper, etc.)
  Livewire/CMW/         # Feature modules: {Module}/{Entity}/{Action}.php
  Models/CMW/
    Master/             # Static data
    Transaction/        # Orders, deliveries, billing
    Inventory/          # Items, stock logs
```

## Quick Reference Locations

- **Flux component docs**: `docs/flux/components/{component}.md` (read before using)
- **Helper responsibilities**: See section 13 in original copilot-instructions.md
- **Event system**: Pattern `shp.{module}.{entity}.{action}`
- **Verification scripts**: Use `temp_debug/` folder, never `php artisan tinker` for verification
