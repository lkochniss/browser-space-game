# Feature Documentation

Stub. Feature docs werden erstellt sobald Tickets `Done`.

| File | Domain | Summary |
|------|--------|---------|
| _(none yet)_ | | |

## Source of Truth (raw concept docs)

`/docs/*.md` — Obsidian-Notes mit Konzepten (DE). Sind NICHT die Feature-Docs hier — sie sind Vision/Lore. Diese Datei spiegelt was tatsächlich implementiert ist.

## Domains in `src/`

| Folder | Status |
|--------|--------|
| `Player/` | Player + CreatePlayer flow |
| `Planet/` | Planet entity + Generate/Claim commands |
| `Building/` | Building model + production helpers (only IRON_MINE) |
| `Resource/` | Resource + Deposit + Production config (only IRON_ORE) |
| `Tick/` | TickEngine + ResourceProductionProcessor |
| `GameState/` | GameState wrapper |
| `Simulation/` | PlayerStartUpScenario (CLI demo) |
| `Common/` | CommandBus, Clock, base interfaces |
