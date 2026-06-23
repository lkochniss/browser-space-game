# T-080 Loot-System (Drop-Tabellen pro Faction)

**Type:** Feature
**Epic:** NPC Factions
**Domain:** Faction
**Blocked By:** T-073, T-103
**Status:** Draft
**Effort:** M
**Depends on:** T-073 (Faction), T-103 (Battle)
**Blocks:** T-074, T-075, T-077

## Beschreibung
Nach gewonnenem PvE-Battle: Loot dropt auf Sieger-Account. Drop-Tabelle pro Faction + Threat-Level.

Loot-Types:
- Resources (Iron-Bar, Steel, AI-Core, Antimaterie etc.)
- Tech-Fragments (für Forschung-Boost in entsprechendem Branch)
- Blueprints (uniques: spezielle Schiff-Mods, Buildings)
- Cosmetics (Banner, Flag, Title — T-141)
- Reputation (zur eigenen Faction +, zur Gegner-Faction sinkt)

## Acceptance Criteria
- [ ] LootTable-Entity (factionId, threatLevel-range, drops-Array<ResourceType, weight, qty-range>)
- [ ] LootRollService: Würfelt pro Battle-Sieg auf Drop-Tabelle, instanziert Loot-Items
- [ ] Loot landet auf Sieger-Planet-Storage (Cargo-Transfer via T-015)
- [ ] Multi-Player-Battle (T-077): Loot proportional zu Damage-Contribution
- [ ] Tech-Fragments: spezieller ResourceType, einlösbar gegen RP-Boost (T-069)
- [ ] Blueprint-Drop: Player-Inventory (separate Table BlueprintLibrary)

## Affected Tests
- tests/Loot/Service/LootRollTest.php
- tests/Loot/Service/LootDistributionTest.php (multi-player)

## Fixtures Needed
Yes — Loot-Tables seeded pro Faction

## Notes
- Drop-Rates conservative — Magic-Item-Inflation vermeiden
- Blueprint-Drops sehr selten, einzigartige Effekte
