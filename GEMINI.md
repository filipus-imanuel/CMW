# SHP ERP - Gemini Quick Reference

Purpose: keep this file short and avoid duplicated instruction payload.

## Canonical Instruction Sources

- Boost baseline: `AGENTS.md`
- Copilot quick reference: `.github/copilot-instructions.md`
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

## Project Core Constraints (Quick)

- Use Flux components for UI.
- Use Livewire `$inputs[]` pattern.
- Authorize modal actions in `openModal()`, not `mount()`.
- DataTable actions column is first and uses `components.datatables.datatable-action`.
- Wrap DB mutations in transactions and recalculate totals server-side.
- Use double gate: `@can()` + `$this->authorize()`.
- Use `WithFileUploads` for upload components.
- Use event naming `shp.{module}.{entity}.{action}`.
- Use `temp_debug/` scripts for verification/debugging.

## Numeric & Upload Defaults

- Flux table numeric headers: `text-center`.
- Flux table numeric cells: `text-right tabular-nums`.
- Numeric display: 2 decimals.
- Default upload max size: 10MB (`max:10240`).

## Maintenance

- Do not duplicate long rule sections here.
- Keep this file under ~120 lines.
- Add cross-cutting rules to `.ai/guidelines/*`.
- Add domain rules to `.ai/skills/{domain}/SKILL.md`.
- See `.ai/MIGRATION.md` for mapping/rationale.
