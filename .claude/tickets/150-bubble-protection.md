# T-150 Bubble-Schutz (Tutorial-Phase bis 2. Planet)

**Type:** Feature
**Epic:** Game Balance
**Domain:** Player
**Blocked By:** T-014
**Status:** Done (Foundation; Effekte + Catch-Up in T-150b split)
**Effort:** M
**Depends on:** T-014 (Kolonisation, Done)
**Blocks:** T-074 (Pirat-Spawn pausiert in Bubble), T-150b

## Beschreibung
Newbie-Schutz: bis zur Kolonisation des 2. Planeten ist Spieler in Bubble-Phase. **Keine NPC-Encounters, keine Spieler-Interaktionen** — pure Tutorial-Modus mit Mining/Buildings/Forschung.

Tutorial-Gate via T-014 Kolonisation: Bubble endet erst wenn Spieler Kolonisationsschiff baut + erfolgreich 2. Planet kolonisiert.

## Acceptance Criteria

- [x] Player-Entity: `bubbleStatus: PlayerBubbleStatus` (default BUBBLE)
- [x] Bubble-Exit-Trigger: `ColonizePlanetCommandService` setzt nach Erfolg
      `exitBubble()` wenn `planets.count >= 2`
- [x] Migration `Version20260622000003` + Backfill für existing Player mit
      >= 2 Planeten (direkt EXITED)
- [x] Tests: Model-Unit + Persistence-Roundtrip + Colonize-Auto-Exit-IT
- [x] Doc `player.md`-Sektion (sobald `player.md` existiert; siehe Notes)

## Out of Scope (in T-150b verschoben)

- **Catch-Up-Mining-Multiplier** (×1.5 für 14d) — braucht `createdAt` oder
  `bubbleExitedAt` Timestamp (Q1 in T-150b)
- **PirateSpawnService skip** (T-074 Draft)
- **OutpostAttacks skip** (T-075 Draft)
- **AuctionService block** (T-111 Draft)
- **Galaxy-Map-Filter** (T-160 Draft)
- **Notification bei Exit** (T-161 Draft)

## Notes

- "bis 2. Planet" statt zeitbasiert (Decision): Spieler-Tempo respektieren
- Foundation-Flag setzt sich automatisch — alle Skip-Effekte-Services lesen
  `player.isInBubble()` sobald sie existieren
