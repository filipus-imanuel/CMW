<laravel-boost-guidelines>

# Laravel Boost Baseline + SHP Modular Rules

Purpose: keep this file concise and avoid duplicated rules.

## Canonical Structure

- **Boost baseline**: this file (high-level)
- **Project global rules**: `.ai/guidelines/*.md`
- **Project domain skills**: `.ai/skills/{domain}/SKILL.md`
- **Migration reference**: `.ai/MIGRATION.md`

## Rule Priority

1. Project-specific rules in `.ai/guidelines/*` and `.ai/skills/*`
2. Laravel Boost baseline in this file
3. General best practices

If conflicts happen, follow project-specific rules unless conflict is about tool availability.

## Boost Tooling Baseline (Keep)

- Use `search-docs` first for Laravel ecosystem docs.
- Use `list-artisan-commands` before running unfamiliar Artisan commands.
- Use available Boost tools for URLs, logs, and database/tinker workflows when relevant.

## Core Laravel Baseline (Keep)

- Follow Laravel 12 structure and conventions.
- Use Form Requests for validation when pattern in module requires it.
- Use authorization checks in server actions.
- Prefer Eloquent relationships and avoid unnecessary raw queries.
- Run targeted tests for each change.
- Run `vendor/bin/pint --dirty` before finalizing.

## SHP Project Constraints (Quick)

- Use Flux components for UI.
- Use Livewire `$inputs[]` pattern for form state.
- Modal authorization belongs in `openModal()`, not `mount()`.
- DataTable actions column must be first and use `components.datatables.datatable-action`.
- Wrap DB mutations in transactions and recalculate totals server-side.
- Use double gate: `@can()` + `$this->authorize()`.
- Use `WithFileUploads` for upload components.
- Use event naming `shp.{module}.{entity}.{action}`.
- Use `temp_debug/` scripts for project verification/debugging flow.

## Numeric Table Rule

- Flux table numeric headers: `text-center`.
- Flux table numeric cells: `text-right tabular-nums`.
- Display numeric values with 2 decimals.

## File Upload Default

- Default max upload size: 10MB (`max:10240`).

## Where Detailed Rules Live

### Global Guidelines (Always Active)

- `.ai/guidelines/00-rule-precedence.md`
- `.ai/guidelines/10-repo-constraints.md`
- `.ai/guidelines/20-security-audit.md`

### Domain Skills (Contextual)

- `.ai/skills/database-schema-standards/SKILL.md`
- `.ai/skills/livewire-crud-patterns/SKILL.md`
- `.ai/skills/flux-ui-components/SKILL.md`
- `.ai/skills/datatable-rappasoft/SKILL.md`
- `.ai/skills/transactions-integrity/SKILL.md`
- `.ai/skills/delete-permissions-flow/SKILL.md`
- `.ai/skills/file-uploads-events/SKILL.md`

## Maintenance Rules

- Do not duplicate long Boost sections here.
- Do not duplicate domain details already in skill files.
- Keep this file short and stable.
- Add new cross-cutting rules to `.ai/guidelines/*`.
- Add new domain rules to `.ai/skills/{domain}/SKILL.md`.

</laravel-boost-guidelines>
