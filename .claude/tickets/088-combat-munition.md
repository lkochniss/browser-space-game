# T-088 Combat-Munition (Verbrauchs-Resources im Battle)

**Type:** Feature
**Epic:** Combat & Battle
**Domain:** Resource
**Blocked By:** T-067, T-103
**Status:** Draft
**Effort:** M (TBD)
**Depends on:** T-067 (Tier-2), T-103 (Battle-Engine)
**Blocks:** —

## Beschreibung

Combat-Verbrauchs-Resources. Schiffe ohne Munition haben drastisch reduzierten Damage-Output (×0.3). Schafft Resource-Pressure auf Combat-Aktivität.

Neue Resources (REFINED, Tier-2-Outputs):
- BALLISTIC_AMMO (Standard-Munition für Frigate/Destroyer-Beam-Waffen)
- WARHEAD (Missile-Munition für Cruiser/Battleship)
- PLASMA_CHARGE (Energie-Munition für Carrier-Fighter + Late-Tier-Weapons)
- POINT_DEFENSE_MAG (Defense-Building-Munition T-068)

Recipe-Vorschläge:
- BALLISTIC_AMMO = Steel + Copper-Bar (2:1)
- WARHEAD = Steel + Tritium-Ore + Chip (3:1:1)
- PLASMA_CHARGE = Plasma-Cell + Composite (1:2) — Tier-3-gated via T-115

## Acceptance Criteria

- [ ] TBD: Neue ResourceTypes BALLISTIC_AMMO, WARHEAD, PLASMA_CHARGE, POINT_DEFENSE_MAG
- [ ] TBD: Manufacturing-Buildings: AMMO_FACTORY, WARHEAD_PLANT, PLASMA_FORGE
- [ ] TBD: Battle-Engine (T-103) verbraucht Munition pro Round — fehlt Munition → Damage ×0.3
- [ ] TBD: Munition-Capacity pro Schiff (Cargo-Slot oder eigene Bay)
- [ ] TBD: Re-Supply nur in eigenem System / Allianz-Station (T-093)

## Open Questions

- Munition-Verbrauchs-Rate pro Combat-Round? (Tuning)
- Verschiedene Munition pro Schiff-Klasse oder universal?
- Defense-Building-Munition (T-068) hat eigene Re-Supply-Logik?

## Notes

- Resource-Drain als Anti-Spam-Sicherung: Combat ist teuer, nicht beliebig wiederholbar
- Auction-House (T-111) wird zu Munition-Markt — Combat-Spieler kauft, Industrie-Spieler verkauft
- Loot-Drops (T-080): besiegte NPC droppen geringe Munition-Mengen
