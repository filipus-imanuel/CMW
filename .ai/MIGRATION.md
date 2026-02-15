# AI Guidelines Migration Matrix

This document maps original instruction rules from AGENTS.md and .github/copilot-instructions.md to the new modular structure in `.ai/guidelines/*` and `.ai/skills/*`.

## Migration Status

✅ **Completed**: All domain rules extracted and organized
📍 **Canonical Source**: AGENTS.md (lines 583-945) for SHP-specific rules
🔄 **Boost Rules**: Remain in AGENTS.md (lines 1-582), not duplicated

## Global Guidelines Mapping

| Original Source | Section | New Location | Lines |
|-----------------|---------|--------------|-------|
| AGENTS.md L47-62 | Boost tool priority, tinker, search-docs | `.ai/guidelines/00-rule-precedence.md` | All |
| copilot L9-49 | Quick Start, debugging, tech stack | `.ai/guidelines/10-repo-constraints.md` | 1-56 |
| copilot L638-701 | Pre-commit checklist, security, gotchas | `.ai/guidelines/20-security-audit.md` | All |

## Skill Mappings

### database-schema-standards

| Original Source | Section | Details |
|-----------------|---------|---------|
| copilot L81-99 | Field Type Standards | code, name, remarks, description types |
| copilot L101-114 | Decimal Precision Standards | decimal(13,2) for monetary, decimal(5,2) for tax |
| copilot L116-119 | Model Casts | decimal:2, boolean patterns |
| copilot L121-123 | UI Formatting | number_format usage |
| copilot L125-168 | Import Standards | Use statements, no FQCN |
| copilot L170-192 | Model Fillable Standards | Check BaseModel first, no duplication |

### livewire-crud-patterns

| Original Source | Section | Details |
|-----------------|---------|---------|
| copilot L293-360 | Input Properties Pattern | $inputs[] array, not individual properties |
| copilot L362-385 | Modal Authorization | In openModal(), NOT mount() |
| copilot L387-392 | Modal Initialization | In openModal() only |
| copilot L394-400 | CRUD Method Naming | store/update/destroy, not save() |
| copilot L402-426 | Lifecycle with Status Guard | mount() with status check |
| AGENTS L718-760 | Same patterns | Duplicate content consolidated |

### flux-ui-components

| Original Source | Section | Details |
|-----------------|---------|---------|
| copilot L60-79 | Component Quick Reference | Button, Badge, Switch, Checkbox, Date Picker, Table, Modal, Callout |
| copilot L61-62 | Documentation First | Read docs/flux/components/{name}.md |
| copilot L68-70 | Date Picker | Use flux:date-picker, not flux:input type="date" |
| copilot L69 | Table patterns | max-w-md whitespace-pre-line for notes, align-middle for actions |
| copilot L71 | Modal syntax | $this->modal('name')->show() / ->close() |

### datatable-rappasoft

| Original Source | Section | Details |
|-----------------|---------|---------|
| copilot L194-222 | Action Column Pattern | Actions first, plural name, standardized component |
| copilot L224-226 | Custom Action Buttons | Add parameters, not HTML |
| copilot L228-233 | Component Restrictions | NO Flux in format() except icons |
| copilot L235-266 | Relationship Columns | Simple eager load, FK field, format with null check |
| AGENTS L591-673 | Same patterns | Duplicate content consolidated |

### transactions-integrity

| Original Source | Section | Details |
|-----------------|---------|---------|
| copilot L268-281 | Transaction Wrapper | DB::transaction, lockForUpdate |
| copilot L283-290 | Totals Calculation | TransactionHelper, never trust client |
| AGENTS L675-690 | Same patterns | Duplicate content consolidated |

### delete-permissions-flow

| Original Source | Section | Details |
|-----------------|---------|---------|
| copilot L428-469 | Delete Confirmation Pattern | deleteId, destroy(), modal confirmation |
| copilot L471-485 | Delete Modal View | flux:modal with x-on:click close pattern |
| copilot L503-520 | Permissions (Spatie) | PermissionHelper, singular naming, double gate |
| copilot L522-583 | DataTable Action Pattern | Permission checks in columns |
| AGENTS L762-870 | Same patterns | Duplicate content consolidated |

### file-uploads-events

| Original Source | Section | Details |
|-----------------|---------|---------|
| copilot L292-305 | File Uploads | WithFileUploads trait, FileUploadHelper |
| copilot L307-318 | Toast Notifications | Flux::toast variants and positions |
| copilot L320-328 | Event Dispatching | shp.{module}.{entity}.{action} convention |
| AGENTS L692-716 | Same patterns | Duplicate content consolidated |

## Deduplication Summary

**Removed Duplicates**:
- Laravel Boost rules in copilot-instructions.md (L702-1284) → Kept in AGENTS.md only
- SHP rules duplicate between AGENTS.md and copilot-instructions.md → Consolidated into skills
- Repeated patterns (DataTable, Livewire) → Single source in skills

**Preserved**:
- Boost baseline rules in AGENTS.md (L1-582) → Not moved to `.ai/` (vendor-managed)
- Domain-specific business logic → Moved to skills for contextual triggering
- Cross-cutting concerns → Moved to global guidelines (always active)

## Coverage Verification

**All SHP Domains Covered**:
- ✅ Database schema standards (migrations, models, casts)
- ✅ Livewire CRUD patterns (inputs, authorization, lifecycle)
- ✅ Flux UI components (all major components referenced)
- ✅ DataTable patterns (Action column, relationships, permissions)
- ✅ Transactions & integrity (DB transactions, totals, locking)
- ✅ Delete flow & permissions (Spatie, double gate, confirmation)
- ✅ File uploads & events (WithFileUploads, toast, dispatching)
- ✅ Security & pre-commit (checklists, gotchas, event conventions)

## Files Modified in This Migration

**Created**:
- `.ai/guidelines/00-rule-precedence.md`
- `.ai/guidelines/10-repo-constraints.md`
- `.ai/guidelines/20-security-audit.md`
- `.ai/skills/database-schema-standards/SKILL.md`
- `.ai/skills/livewire-crud-patterns/SKILL.md`
- `.ai/skills/flux-ui-components/SKILL.md`
- `.ai/skills/datatable-rappasoft/SKILL.md`
- `.ai/skills/transactions-integrity/SKILL.md`
- `.ai/skills/delete-permissions-flow/SKILL.md`
- `.ai/skills/file-uploads-events/SKILL.md`
- `.ai/MIGRATION.md` (this file)

**To Update**:
- `boost.json` - Add global guidelines to `guidelines` array
- `AGENTS.md` - Add pointer to `.ai/` structure, keep only Boost+pointer
- `.github/copilot-instructions.md` - Add pointer, mark as quick reference

## Rollback Plan

If modular structure causes issues:
1. Revert boost.json to empty guidelines array
2. Original rules remain intact in AGENTS.md and copilot-instructions.md
3. Delete `.ai/` directory
4. No data loss - original files preserved

## Maintenance

**When to update**:
- New domain pattern emerges → Create new skill in `.ai/skills/{domain}/SKILL.md`
- Cross-cutting concern identified → Add to global guideline
- Boost updates → AGENTS.md Boost section only, don't duplicate

**Do NOT**:
- Duplicate Boost rules in skills
- Override Boost tool priorities
- Create skills for one-off patterns (keep in component comments)
