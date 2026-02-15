# SHP ERP System - Copilot Quick Reference

Purpose: keep this file short and delegate details to modular rules.

## Canonical Sources

- Laravel Boost baseline: `AGENTS.md`
- Global always-on rules:
  - `.ai/guidelines/00-rule-precedence.md`
  - `.ai/guidelines/10-repo-constraints.md`
  - `.ai/guidelines/20-security-audit.md`
- Domain skills (contextual):
  - `.ai/skills/database-schema-standards/SKILL.md`
  - `.ai/skills/livewire-crud-patterns/SKILL.md`
  - `.ai/skills/flux-ui-components/SKILL.md`
  - `.ai/skills/datatable-rappasoft/SKILL.md`
  - `.ai/skills/transactions-integrity/SKILL.md`
  - `.ai/skills/delete-permissions-flow/SKILL.md`
  - `.ai/skills/file-uploads-events/SKILL.md`

## Priority Rules

1. Project-specific rules in `.ai/guidelines/*` and `.ai/skills/*`
2. Laravel Boost baseline in `AGENTS.md`
3. General best practices

If rules conflict, follow project rules unless the conflict is about tool availability/invocation semantics.

## Core Project Constraints

- Use Flux components for UI.
- Use Livewire `$inputs[]` pattern for form state.
- Place modal authorization in `openModal()`, not `mount()`.
- DataTable actions column must be first and use `components.datatables.datatable-action`.
- Wrap DB mutations in transactions; recalculate totals server-side.
- Use double gate for permissions: `@can()` + `$this->authorize()`.
- Use `WithFileUploads` when handling uploads.
- Use event naming pattern: `shp.{module}.{entity}.{action}`.
- Use `temp_debug/` scripts for verification/debugging.

## Numeric & Table UI Notes

- Flux table numeric headers: `text-center`.
- Flux table numeric cells: `text-right tabular-nums`.
- Display numeric values with 2 decimals.

## File Upload Defaults

- Default max upload size: 10MB (`max:10240`).

## Maintenance Rules

- Do not duplicate full Boost sections here.
- Do not duplicate full domain rules here.
- Keep this file under ~150 lines.
- Add new rules in the right place:
  - cross-cutting => `.ai/guidelines/*`
  - domain-specific => `.ai/skills/{domain}/SKILL.md`

## Reference

- Migration mapping and rationale: `.ai/MIGRATION.md`
