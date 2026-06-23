# T-082d: Demo-Action-Log Export für KI-Tuning

**Type:** Feature
**Epic:** Demo CLI
**Domain:** Demo
**Blocked By:** T-082, T-082b, T-082c
**Status:** Done
**Effort:** S (~1.5h)
**Depends on:** T-082 (Demo-CLI), T-082b, T-082c
**Blocks:** —

## Beschreibung

Demo-CLI loggt jede User-Aktion + Outcome + State-Snapshot in eine append-only
JSONL-Datei (`var/demo-log.jsonl`). Eine "Export Log"-Menu-Action zeigt den
Log-Pfad und gibt die letzten N Einträge auf stdout aus.

**Zweck:** Datenbasis für KI-driven Balance-Tuning — Cost-Skalierung, Bauzeiten,
Pop-Curves, Salvage-Rates, Recycling-Output-Raten etc. lassen sich aus echten
Spielsessions ableiten statt aus dem Bauchgefühl.

## Acceptance Criteria

- [ ] `DemoActionLogger` Service in `src/Demo/Service/`:
  - `log(string $action, array $params, array $stateSnapshot): void` — append JSON line
  - Datei: `%kernel.project_dir%/var/demo-log.jsonl` (gitignored)
  - JSONL-Format: `{ts, action, params, success, snapshot, error?}`
- [ ] State-Snapshot kompakt aber aussagekräftig:
  - clock_now (für Wallclock-Korrelation)
  - pro Planet: resources-Map, pop {total, assigned, cap}, buildings-Liste mit `{type, level, ready}`
  - ships gesamt: count + types
  - fleets gesamt: count
  - active_research (sobald T-025 done)
- [ ] Hook in InteractiveDemoCommand: Wrapper um Menu-Action-Match-Block — vorher
  Snapshot, nachher Snapshot, beide loggen mit success/error
- [ ] Tick-Forward extra-loggen mit Delta-State (`{seconds, fleetsArrived, salvages, discovered}`)
- [ ] Reset-Hook: bei `--reset` Log umbenennen zu `demo-log-{timestamp}.jsonl.bak` (Vorgeschichte erhalten)
- [ ] Demo-Menu-Action "Export Log":
  - Zeigt File-Pfad
  - Zeigt letzte 20 Einträge auf stdout (kompakt formatiert)
  - Optional: kompletten Log auf stdout via `--full` Flag (oder zweite Sub-Action)
- [ ] Tests:
  - `DemoActionLoggerTest` (Unit, In-Memory-File-Pfad): log-Format, append-Verhalten, Reset-Backup
  - 1 IT-Test der durch Demo-CLI eine Aktion fährt und Log-File-Inhalt prüft
- [ ] Doc: `demo.md` Section "Action-Log" + .gitignore-Eintrag
- [ ] Suite grün

## Decisions (2026-06-19)

1. **State-Snapshot:** vollständig (alle IDs + Timestamps für Wallclock-Tuning).
2. **Format:** pures JSONL — optimal für KI-Parsing.
3. **Rotation:** keine — Demo bleibt überschaubar; Reset macht eh `.bak`.
4. **Sensible Daten:** Demo-only, irrelevant.

## Files

**Neu:**
- `src/Demo/Service/DemoActionLogger.php`
- `tests/Demo/Service/DemoActionLoggerTest.php`

**Geändert:**
- `src/Demo/Command/InteractiveDemoCommand.php` (Logging-Hook im Menü-Loop, +Export-Action,
  +Tick-Forward-Delta-Log)
- `.gitignore` (var/demo-log*.jsonl)
- `.claude/docs/demo.md` (Action-Log Section)

## Out of Scope (Folge-Tickets)

- **Web-UI für Log-Viewer** (T-034 Folge)
- **Log-Auswertung-Reports** (Tabellen aus Log generieren) — separates Tooling-Ticket
- **Replay-Mechanik** (Log re-playen) — TBD
