# T-150b Bubble-Effekte + Catch-Up-Multiplier (Folge zu T-150)

**Type:** Feature
**Status:** Draft
**Effort:** M
**Depends on:** T-150 (Foundation, Done), T-074 (Pirate-Spawn, Draft),
T-075 (Outposts, Draft), T-111 (Auction, Draft), T-160 (Galaxy-Map, Draft),
T-161 (Notifications, Draft)
**Blocks:** —

## Beschreibung

T-150 etabliert nur den `bubbleStatus`-Flag + Auto-Exit-Trigger. Die
eigentlichen Skip-Effekte und der Catch-Up-Mining-Bonus brauchen Hooks in
Services, die noch nicht existieren oder ausgebaut sind.

## Open Questions

### Q1: Catch-Up-Multiplier Zeitbasis

T-150 erwähnt "14 Tage seit Player.createdAt". Player hat heute **kein**
`createdAt`-Feld. Optionen:

- (a) Player.createdAt hinzufügen (Migration + Default-Backfill für existing)
- (b) Pro Player ein `bubbleExitedAt` setzen wenn BUBBLE → EXITED — Catch-Up
  läuft 14d ab dem Exit-Moment (alternative Lese: "Tutorial-Ende statt
  Account-Start")
- (c) Beides — `createdAt` für andere Zwecke, `bubbleExitedAt` für Catch-Up

### Q2: Catch-Up = nur Mining oder mehr?

- (a) Nur Mining (×1.5) — wie T-150 spezifiziert
- (b) Mining + Pop-Growth — schnelleres Aufholen
- (c) Mining + Pop + Construction-Speed — Komplett-Catch-Up

## Acceptance Criteria (Draft — final nach Q1-Q2)

- [ ] Catch-Up-Zeitbasis-Feld (Q1)
- [ ] `BubbleCatchUpMultiplier`-Service: liefert effective Mining-Multi pro
      Player (1.5 wenn aktiv, 1.0 sonst)
- [ ] Integration in MiningProcessor (Tick) — bestehender Code zieht Multi
- [ ] `T-074 PirateSpawnService` skippt BUBBLE-Player (sobald T-074 da)
- [ ] `T-075 Outpost-Attacks` ignorieren BUBBLE-Player (sobald T-075 da)
- [ ] `T-111 AuctionService` blockt BUBBLE-Player (sobald T-111 da)
- [ ] `T-160 Galaxy-Map` filtert auf eigenen Sektor wenn BUBBLE
- [ ] `T-161 Notification` "Welcome to the Galaxy" bei Bubble-Exit
- [ ] Tests

## Out of Scope

- Bubble-Auto-Verlängerung bei Inactivity → T-152
- Allianz-Beitritt vor Exit blockieren → T-052 / T-052-Folge

## Notes

- T-150 ist Foundation und benennt diese Hooks; T-150b implementiert sie
  wenn die jeweiligen Services existieren
