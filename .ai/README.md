# AI Guidelines Structure

This directory contains modular, focused AI coding guidelines for the SHP ERP system.

## Directory Layout

```
.ai/
├── MIGRATION.md                  # Migration matrix & rollback plan
├── README.md                     # This file
├── guidelines/                   # Global rules (always active)
│   ├── 00-rule-precedence.md    # Hierarchy: Boost > Global > Skill
│   ├── 10-repo-constraints.md   # Tech stack, structure, commands  
│   └── 20-security-audit.md     # Pre-commit & security checklists
└── skills/                       # Domain-specific rules (contextual)
    ├── database-schema-standards/
    ├── datatable-rappasoft/
    ├── delete-permissions-flow/
    ├── file-uploads-events/
    ├── flux-ui-components/
    ├── livewire-crud-patterns/
    └── transactions-integrity/
```

## How It Works

### Global Guidelines (Always Active)
Located in `.ai/guidelines/*` - these rules apply to ALL code:
- **00-rule-precedence.md**: Rule hierarchy & conflict resolution
- **10-repo-constraints.md**: Tech stack, directory structure, dev commands
- **20-security-audit.md**: Security checklist, pre-commit checklist, common gotchas

Active via `boost.json` → `guidelines` array.

### Domain Skills (Contextual)
Located in `.ai/skills/{domain}/SKILL.md` - these rules trigger when working on specific domains:
- **database-schema-standards**: Field types, decimal precision, imports, fillable rules
- **livewire-crud-patterns**: $inputs[] pattern, modal auth, lifecycle, CRUD naming
- **flux-ui-components**: Flux Pro component reference, patterns, documentation links
- **datatable-rappasoft**: Action columns, relationships, permission checks
- **transactions-integrity**: DB transactions, locking, totals calculation, sanitization
- **delete-permissions-flow**: Delete confirmation pattern, Spatie permissions, double gate
- **file-uploads-events**: WithFileUploads trait, toast notifications, event dispatching

Skills are **NOT globally active** - AI applies them contextually when relevant.

## Principles

1. **To-the-point**: Only actionable rules, no narrative fluff
2. **No duplication**: Each rule lives in exactly one file
3. **No Boost override**: Skills never contradict Laravel Boost baseline
4. **Focused files**: Guidelines < 60 lines, Skills < 150 lines each

## Source Files

**Canonical source**: `AGENTS.md` (SHP-specific rules) + Laravel Boost (vendor rules)

**Quick reference**: `.github/copilot-instructions.md` (pointers + legacy patterns)

**Auto-generated**: `GEMINI.md` (derived artifact, not canonical)

## Maintenance

**Adding new patterns**:
- Cross-cutting concern → Add to global guideline
- Domain-specific pattern → Create new skill in `.ai/skills/{domain}/SKILL.md`
- One-off pattern → Keep in code comments, don't create skill

**Updating existing rules**:
- Check `MIGRATION.md` for source mapping
- Update skill file directly
- Never duplicate Boost rules

## Rollback

If modular structure causes issues:
1. Revert `boost.json` to empty `guidelines` array
2. Original rules remain in `AGENTS.md` and `copilot-instructions.md`
3. Delete `.ai/` directory
4. No data loss - originals preserved

## Migration Details

See `MIGRATION.md` for:
- Complete mapping: Original Source → New Location
- Line-by-line tracking
- Deduplication summary
- Coverage verification
