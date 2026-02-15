# Security & Pre-Commit Audit

## Security Checklist (MANDATORY)

- [ ] **Double gate**: Always pair `@can()` in Blade with `$this->authorize()` in component method
- [ ] **Never trust client totals**: Recalculate server-side using TransactionHelper
- [ ] **Validate file uploads**: Check MIME type, size, use FileUploadHelper
- [ ] **Sanitize numeric input**: Always use `sanitize_numeric()` before arithmetic
- [ ] **Lock financial aggregates**: Use `lockForUpdate()` in transactions
- [ ] **Audit trail**: Populate `created_by`, `updated_by`, `deleted_by` fields

## Pre-Commit Checklist

- [ ] DB migration + model `$fillable`/`$casts` updated
- [ ] Validation rules centralized in component `rules()` method
- [ ] Permissions added to `PermissionHelper::master()` + ran `php artisan permission:sync`
- [ ] Transaction wrapper for financial mutations
- [ ] Events dispatched after state changes (`$this->dispatch('shp.{module}.{entity}.{action}')`)
- [ ] Routes added to `routes/web.php`
- [ ] Navigation added to `sidebar.blade.php` (sorted A-Z within group)
- [ ] Modal Create/Edit: Authorization in `openModal()` NOT `mount()`
- [ ] Action buttons wrapped in `@can()`, DataTable columns check permissions
- [ ] No debug calls (`dd()`, `dump()`, `var_dump()`)
- [ ] Ran `vendor/bin/pint --dirty` to fix code style

## Common Gotchas

- **Stale totals**: Forgot to dispatch refresh event after mutations
- **Double formatting**: Don't run `format_number()` on already formatted input
- **Status guard**: Always add status check in `mount()` to prevent editing wrong status
- **Stock check**: Use `getTotalAvailableSack()` for availability, not `getTotalAvailableQty()`
- **File uploads**: ALWAYS add `WithFileUploads` trait to Livewire component

## Event System Convention

**Naming**: `shp.{module}.{entity}.{action}`

**Pattern**: 
1. Mutation in DB::transaction
2. Refresh model: `$model = $model->fresh(['relations'])`
3. Dispatch event: `$this->dispatch('shp.sales.order.refresh')`
4. Listener refreshes and repopulates

**Examples**:
- `shp.sales.request.edit.refresh_order_detail`
- `shp.sales.billing.show.refresh`
- `shp.production.refresh_intermediates`
