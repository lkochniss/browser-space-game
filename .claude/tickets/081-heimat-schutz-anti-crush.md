# T-081 Heimat-Schutz (Anti-Crush-Foundation)

**Type:** Feature
**Epic:** Game Balance
**Domain:** Planet
**Blocked By:** T-007
**Status:** Done (Foundation; Effekte in T-081b split)
**Effort:** M
**Depends on:** T-007 (SolarSystem, Done)
**Blocks:** T-081b, T-101b (Abandon-Block für Heimat)

## Beschreibung
Start-Planet hat strukturelle Anti-Crush-Garantien. Ziel: kein Spieler verliert sein gesamtes Setup nach einer einzelnen verlorenen Schlacht. PvE-Loss-Cap.

## Acceptance Criteria

- [x] Planet bekommt `isHomePlanet: bool` (default false)
- [x] `Planet::markAsHome()` Methode (idempotent)
- [x] `ClaimStartPlanetCommandService` markiert Start-Planet automatisch als Heimat
- [x] Migration `Version20260622000004` mit Backfill: pro Player den ersten
      Planeten (`ORDER BY id ASC`) als Heimat markieren (idempotent)
- [x] Tests: Model-Unit + Persistence-Roundtrip + Claim-IT
- [x] Doc `planets.md` Heimat-Sektion

## Out of Scope (in T-081b verschoben)

- **Pop-Loss-Cap** (10%) — braucht Battle-Engine (T-103 Draft)
- **Resource-Vault** (30% nicht raubbar) — braucht LootRollService (T-080 Draft)
- **Building-Damage-Cap** — braucht Battle-Engine
- **Shield-HP + Cooldown** — braucht Shield-State + Battle-Engine
- **Sensor-Pre-Attack-Warning** — braucht T-068 + T-161
- **Heimat-Verlegen-Command** (Q1 in T-081b) — Open Question Permanent vs Move
- **Per-Player-Uniqueness-Enforcement** — Application-Layer in T-081b

## Notes
- Sekundäre Planeten (kolonisiert) haben keine Anti-Crush-Garantien — strategischer Trade-off
- Vault-Mechanik wird live-computed (kein DB-Field) — bleibt T-081b
- T-101b Abandon-Mechanik liest `isHomePlanet` für Q3 (Heimat-Schutz)
