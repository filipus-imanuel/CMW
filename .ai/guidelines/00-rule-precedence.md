# Rule Precedence & Conflict Resolution

## Priority Hierarchy

1. **Project-specific rules** (`.ai/guidelines/*.md` + `.ai/skills/*/SKILL.md`)
2. **Laravel Boost baseline** (from vendor/laravel/boost)
3. **General best practices**

**Rationale**: Project rules are intentional decisions made with full SHP ERP context. They override generic guidance when conflicts arise.

## Conflict Resolution Policy

- **When Boost conflicts with project rules**: Follow project rules (they're made for specific SHP requirements)
- **When global conflicts with skill**: Follow skill (more specific context wins)
- **When skills conflict**: Follow the most recently applied/relevant skill for the current task
- **Exception - Boost tooling**: Follow Boost instructions for tool usage (search-docs, tinker, list-artisan-commands) - these aren't "rules" but "available tools"

## Boost Tooling (Not Overridable)

**These are tool instructions, not rules** - always follow:
- Use `search-docs` first for Laravel ecosystem packages (gets version-specific docs)
- Use `list-artisan-commands` to verify Artisan command parameters
- For debugging/verification: Use `temp_debug/` folder with standalone PHP scripts (more reliable than tinker)

## Boost Patterns (Project Can Override)

**These can be overridden by project rules when needed:**
- Test enforcement patterns
- Code style preferences (if project has stricter standards)
- Architecture patterns (if project has established conventions)
- File organization (if project structure differs)

Project rules in `.ai/guidelines/*` and `.ai/skills/*` take precedence when they explicitly contradict Boost suggestions.

## When to Apply Skills

Skills are **contextually triggered**, not globally active:
- Apply when working on specific domain (e.g., DataTable when editing IndexDataTable.php)
- Guidelines are **always active** for cross-cutting concerns
- If unsure, prefer guideline rules over inventing new patterns
