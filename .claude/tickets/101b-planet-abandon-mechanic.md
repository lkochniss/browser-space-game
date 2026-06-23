# T-101b Planet-Abandon-Mechanik (Folge zu T-101)

**Type:** Feature
**Epic:** Game Balance
**Domain:** Planet
**Blocked By:** T-101
**Status:** Draft
**Effort:** M
**Depends on:** T-101 (Planet-Cap, Done)
**Blocks:** —

## Beschreibung

T-101 (Planet-Cap) deckt den Cap-Check beim Kolonisieren ab. Wenn Player am
Cap ist, kann er aktuell nicht weiter expandieren — es gibt keinen Mechanismus
um einen Planeten aktiv aufzugeben, um einen Slot freizumachen.

Lore: Player evakuiert die Kolonie, alle Resources/Buildings/Pop gehen
verloren, der Planet wird zu einem unclaimed POI im selben Solar-System
(andere Player können ihn neu kolonisieren).

## Open Questions

### Q1: Was passiert mit Buildings + Resources beim Abandon?

- (a) **Hard-Reset**: alle Buildings + Resources gelöscht, Pop killed,
  Planet neu generiert (Type/Size bleibt)
- (b) **Hard-Reset + Trümmerfeld**: wie (a), aber zusätzlich ein DebrisField
  POI im selben Solar-System mit Recycling-bar Material
- (c) **Soft-Abandon**: Buildings bleiben aber unzugänglich, Player kann
  später wieder claimen — quasi Pause-Modus

### Q2: Cooldown / Spam-Prevention?

- (a) **Kein Cooldown** — Player darf jederzeit; Cap-Logik sorgt für Limit
- (b) **24h Cooldown pro Player** — verhindert hot-swap-Exploit
- (c) **Resource-Cost zum Abandon** — kostet z.B. 1000 IRON_BAR Evakuation

### Q3: Heimat-Planet Schutz?

- (a) **Heimat darf nicht abandoned werden** (T-081 Heimat-Schutz-Pattern)
- (b) **Heimat ist normal abandonable** — Player startet dann auf einem
  anderen Planet als neue Heimat
- (c) **Wenn alle Planeten abandoned → Player ist game-over** (extreme)

### Q4: UI / API?

- AbandonPlanetCommand-Pfad?
- Demo-CLI: separate Action "Planet aufgeben" mit Confirm-Dialog?

## Acceptance Criteria (Draft — final nach Q1-Q4)

- [ ] `AbandonPlanetCommand(playerId, planetId)` + Handler + Service
- [ ] Validation: Player owns planet, Heimat-Schutz (Q3), Cooldown (Q2)
- [ ] Effect: Buildings/Resources/Pop reset, Planet zurück zu unclaimed-State
- [ ] Eventuell `DebrisField`-Spawn (Q1b)
- [ ] Demo-CLI Action mit Confirm-Dialog
- [ ] `PlanetCapReachedException` Message kann auf Abandon-Mechanik
  hinweisen ("Cap erreicht — Planet aufgeben oder Logistics-Forschung")
- [ ] Tests
- [ ] Doc `planets.md` Abandon-Sektion

## Out of Scope

- Multi-Player-Abandoned-Capture (zukünftiges Battle-Feature)
- Auto-Abandon bei Inactivity (T-152)

## Notes

- Foundation in T-101 reicht für Game-Start; Abandon ist Endgame-Mechanik
- Synergie mit T-081 Heimat-Schutz (kann auch dort als Bundle gelöst werden)
