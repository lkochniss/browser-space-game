# T-024: Raumschlacht-Resolution (PvE-Pivot)

**Type:** Feature
**Epic:** Combat & Battle
**Domain:** Ship
**Blocked By:** T-017, T-021, T-073
**Status:** Superseded by T-103 (T-167 Cleanup)
**FX:** No
**MIG:** No
**Depends on:** T-017 (Flotte), T-021 (Trümmerfeld), T-073 (Faction)
**Blocks:** T-074, T-075, T-077, T-080

## Description

**Pivot zu PvE-only**: ursprünglicher Scope adressierte beliebige Fleet-vs-Fleet-Begegnungen. Game-Design-Decision: kein PvP — nur Spieler vs NPC-Faction-Flotten (Pirate, Renegade, Xenos).

Dieses Ticket wird ersetzt/abgelöst durch **T-103 Battle-Resolution-Engine** (Round-based mit Tactic-Choice + Counter-System).

T-024 bleibt offen als Verweis-Anker bis T-103 implementiert ist; danach kann T-024 als "duplicate of T-103" geschlossen werden.

## AC (legacy, voraussichtlich nicht mehr separat umgesetzt)

- [ ] ~~`BattleResolver` Service deterministisch/seedbar~~ → ersetzt durch T-103
- [ ] ~~Input zwei Fleet~~ → T-103 (Player-Fleet vs NPC-Fleet, niemals Player-vs-Player)
- [ ] ~~Output BattleResult~~ → T-103 BattleEntity mit Round-Log + Survivors + Loot
- [ ] DebrisField-Erzeugung → bleibt relevant, integriert in T-103 Loot-Phase
- [ ] Pop-Tod auf Heimat → wird in T-081 Heimat-Schutz mit Loss-Cap (max 10%) versehen

## Notes

- **PvE-Decision finaler Stand**: kein Player-Player-Combat mehr im Game-Design. Battle-Engine refused Player-Targets per Type-Validation
- T-103 ist Reimplementation mit ausgereiftem Scope (Tactics, Captains, Multi-Player-PvE-Boss)
- T-024 bleibt als Cross-Reference für DebrisField-Erzeugung (T-021) → in T-103 sicherstellen dass Battle-Loot DebrisField anlegt
