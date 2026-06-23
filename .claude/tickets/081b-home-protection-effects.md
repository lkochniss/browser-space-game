# T-081b Heimat-Schutz-Effekte (Folge zu T-081)

**Type:** Feature
**Epic:** Game Balance
**Domain:** Planet
**Blocked By:** T-081, T-103, T-080
**Status:** Draft
**Effort:** M-L
**Depends on:** T-081 (Foundation, Done), T-103 (Battle-Engine), T-080 (Loot),
T-068 (Defense-Buildings), T-161 (Notifications)
**Blocks:** —

## Beschreibung

T-081 etabliert nur den `isHomePlanet`-Flag. Die eigentlichen Anti-Crush-
Mechaniken hängen alle an Services, die noch nicht existieren:

- **Pop-Loss-Cap** (max 10% Pop pro Defense-Battle) braucht Battle-Engine
- **Resource-Vault** (30% jeder Resource nicht raubbar) braucht Loot-Service
- **Building-Damage-Cap** (max 1 Defense-Building zerstört/Battle) braucht Battle
- **Shield-Cooldown** (24h Reload nach Defense-Battle) braucht Shield-State
- **Pre-Attack-Warning** (Sensor-Array Vorlauf) braucht T-068 + T-161

## Open Questions

### Q1: Heimat-Verlegen erlauben?

T-081 spec sagt "permanent set". Aber Late-Game-Spieler will vielleicht Heimat
auf besseren Planet verlegen.

- (a) **Permanent** (T-081 default) — kein Verlegen, Heimat ist Start-Planet bis Game-Over
- (b) **Costly-Move** — Heimat verlegen kostet 7d Cooldown + Massive-Resources
- (c) **One-Move-Per-Account** — einmaliger Heimat-Wechsel erlaubt

### Q2: Mehrfach-Heimat?

- (a) Genau 1 Heimat pro Player (per-Player-Uniqueness via Service-Layer)
- (b) Mehrere Heimat-Slots erlaubt mit Tech-Forschung (z.B. T-101 cap-Erweiterung)

## Acceptance Criteria (Draft — final nach Q1-Q2 + Hooks ready)

- [ ] Pop-Loss-Cap (10%) in Battle-Resolver (T-103) für `isHomePlanet`
- [ ] Resource-Vault (30%) im LootRollService (T-080) — live-computed,
      kein DB-Field, vom is_home_planet abhängig
- [ ] Building-Damage-Cap (max 1 Defense-Building zerstörbar/Battle)
- [ ] Shield-HP-Pool + 24h Cooldown nach Defense-Battle
- [ ] Sensor-Array-Warning (T-068) ergänzt Pre-Attack-Notification (T-161)
- [ ] Heimat-Verlegen-Command (Q1) — falls implementiert
- [ ] Per-Player-Uniqueness-Validierung (Q2) im ColonizePlanetCommandService
      oder MarkAsHomeService
- [ ] T-101b: Abandon-Block für Heimat-Planet
- [ ] Tests

## Out of Scope

- T-093 Allianz-Stationen mit Heimat-Schutz-ähnlicher Mechanik

## Notes

- T-081 ist Foundation und benennt diese Hooks; T-081b implementiert sie
  wenn die jeweiligen Services existieren
- Vault-Mechanik wird live-computed, kein eigenes DB-Field (nur LootRoll
  filtert basierend auf `planet.isHomePlanet`)
