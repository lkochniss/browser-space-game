# T-103e Loot-Trigger-Hook (Folge zu T-103 + T-080)

**Type:** Feature
**Epic:** Combat & Battle
**Domain:** Ship
**Blocked By:** T-103, T-080
**Status:** Draft
**Effort:** S
**Depends on:** T-103 (Battle-Foundation), T-080 (LootRollService)
**Blocks:** —

## Beschreibung

T-103 Battle-End-Hook ruft T-080 LootRollService → Resources/Tech-Fragments/
Blueprints landen auf Sieger-Account.

## Open Questions

### Q1: Multi-Player-Battle Loot-Split

- Wenn mehrere Player gegen NPC (T-077 World-Boss-Raid): Loot proportional
  zu Damage-Contribution? Oder gleicher Split? Oder Damage-Threshold-Tickets?

### Q2: Failed-Loot-Cap

- Was passiert wenn Loot-Resource den Player-Volume-Cap (T-177) überschreitet?
- Spillover-Loss (Items verloren)? Wartet in "Loot-Inventory"? Auction-Auto?

### Q3: Captain-Personality-Loot-Bonus

- Veteran-Captain hat Loot-Bonus-Skill (T-104b/c) — hookt sich hier ein?
  Oder eigener Service?

## Acceptance Criteria (Draft — final nach Q1-Q3)

- [ ] `BattleResolver` dispatcht `BattleEndedEvent` (Symfony Messenger)
- [ ] `LootRollEventListener` (T-080) konsumiert Event, rollt Drops
- [ ] Loot landet auf Sieger-Planet-Storage via T-177 Volume-API
- [ ] Multi-Player-Distribution (Q1)
- [ ] Tests: Battle-Win triggert Loot

## Out of Scope

- LootRollService selbst (T-080)
- World-Boss-Multi-Damage-Tracking (T-077)
