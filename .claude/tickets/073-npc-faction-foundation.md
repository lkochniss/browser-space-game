# T-073: NPC-Faction-Foundation

**Type:** Feature
**Epic:** NPC Factions
**Domain:** Faction
**Blocked By:** None
**Status:** Done
**FX:** No (separate Seed-Service via `FactionSeedService`, kein Doctrine-Fixtures-Bundle nötig)
**MIG:** Yes (factions + player_faction_reputation Tabellen)
**Blocks:** T-074, T-075, T-078, T-080, T-111, T-112, T-113, T-114, T-120, T-131 (NPC-PvE-Stack + NPC-Wirtschaft)

## Beschreibung

Setting: Warhammer-40k-inspiriert. Alle Spieler = Imperiale Menschen (implizit). NPC-Factions sind nicht-spielbare Gruppen mit eigener Agenda. Hostiles greifen Spieler nicht proaktiv an — sie sind World-Events zum Farmen. Neutrale Händler-Gilde bietet Reputation-Aufbau via Quests.

T-073 ist die kleinste Foundation: Faction-Domain + Reputation-Storage + Default-Seeds. Kein Game-Effekt heute — Wirkung kommt mit Folge-Tickets (T-074 Pirat-Encounter, T-075 Outposts, T-078 Quests).

## Faction-Liste (Default-Seed)

| Slug | Name | Type | Hostile | Default-Rep | Rolle |
|------|------|------|---------|-------------|-------|
| `merchant_guild` | Galaktische Händler-Gilde | MERCHANT_GUILD | nein | 0 | Neutral, Trade/Transport-Quests, Rep-aufbaubar |
| `pirate_consortium` | Pirat-Konsortium | PIRATE | ja | -100 | Easy-Tier Loot |
| `renegade_warbands` | Abtrünnige | RENEGADE | ja | -100 | Mid-Tier, droppt Imperial-Tech |
| `xenos_splinter` | Xenos-Splitter | XENOS | ja | -100 | High-Tier, exotische Tech |

## AC

- [x] `FactionId` ValueObject (UUID-basiert via `AbstractUuid`)
- [x] `FactionType` Enum (PIRATE, RENEGADE, XENOS, MERCHANT_GUILD)
- [x] `ReputationTier` Enum (HOSTILE/NEUTRAL/FRIENDLY/ALLIED) + `forValue(int)` static (Bands -100..-30 / -29..29 / 30..69 / 70..100)
- [x] `Faction` Entity mit slug (unique) + name + type + isAlwaysHostile + defaultReputation + description
- [x] `PlayerFactionReputation` Entity mit composite-PK (player_id + faction_id) + value + Range-Validation [-100, 100]
- [x] `FactionRepository` + `PlayerFactionReputationRepository`
- [x] `FactionIdType` (Doctrine-Custom-Type) + Registrierung in `doctrine.yaml`
- [x] `ReputationService`:
  - `getReputation(player, faction)`: lazy — gibt `defaultReputation` zurück wenn keine Row
  - `getTier(player, faction)`: liefert Tier
  - `changeReputation(player, faction, delta)`: legt Row lazy an, addiert delta, clamp [-100, 100], wirft `HostileFactionRepLockedException` wenn `isAlwaysHostile`
- [x] `HostileFactionRepLockedException`
- [x] `FactionSeedService::seed()` idempotent (find-by-slug, insert-if-missing) für die 4 Default-Factions
- [x] Migration `Version20260619000003`: factions + player_faction_reputation Tabellen
- [x] Tests:
  - [x] Unit: `ReputationTier::forValue` Boundaries (`tests/Faction/ValueObject/ReputationTierTest.php`)
  - [x] Unit: `Faction::isAlwaysHostile` getter (`tests/Faction/Model/FactionTest.php`)
  - [x] IT: `FactionSeedService::seed()` idempotent (`tests/Faction/Service/FactionSeedServiceTest.php`)
  - [x] IT: `ReputationService::getReputation` liefert default ohne Row
  - [x] IT: `ReputationService::changeReputation` persistiert + clamped (upper + lower bound)
  - [x] IT: `ReputationService::changeReputation` wirft für hostile-Faction
- [x] `IntegrationTestCase` ruft `FactionSeedService::seed()` nach Schema-Create
- [x] Bestehende Tests grün (220/220, 441 assertions)

## Geklärte Fragen

1. **Faction-Anzahl:** 4 (1 neutral + 3 hostile). Imperium implizit, kein Faction-Eintrag.
2. **Reputation-Init:** Lazy. Row wird erst bei erster Mutation angelegt; Read liefert `defaultReputation` als Fallback.
3. **Hostile-Lock:** isAlwaysHostile=true → Setter wirft Exception bei Änderungsversuch.
4. **Reputation-Scope:** Pro Spieler. Allianz-Member haben unabhängige Reputationen.
5. **Spawn-Strategie:** Hybrid (statische HQs + dynamische Flotten/Outposts) — kommt mit T-074/T-075. T-073 nur Domain-Foundation.

## Out of Scope (Folge-Tickets)

- **T-074** Pirat-Encounter-Spawn (Random-Pirat-Flotten in Systemen)
- **T-075** Alien-Outposts (Statische POI-Threats mit Loot)
- **T-078** Faction-Quests (Reputation-Aufbau via Missionen)
- **T-080** Loot-System (Drop-Tabellen pro Faction)
- **T-091** Allianz-Bank (Reputation-Sharing-Diskussion: NICHT in T-073)
- Faction-Heimat-Systeme (T-075-Folge)
- Faction-Beziehungen untereinander (T-084 Galactic-Council)
- Reputation-Decay über Inactivity (Folge-Ticket bei Bedarf)

## Implementation-Status

**Code (gerade gebaut, ungetestet):**
- `src/Faction/ValueObject/FactionId.php`, `FactionType.php`, `ReputationTier.php`
- `src/Faction/Model/Faction.php`, `PlayerFactionReputation.php`
- `src/Faction/Repository/FactionRepository.php`, `PlayerFactionReputationRepository.php`
- `src/Faction/Service/ReputationService.php`, `FactionSeedService.php`
- `src/Faction/Exception/HostileFactionRepLockedException.php`
- `src/Common/Doctrine/Type/FactionIdType.php` + `doctrine.yaml`-Registrierung

**Offen:**
- Migration-File schreiben
- IntegrationTestCase erweitert um Faction-Seed nach Schema-Create
- Tests schreiben
- Schema-Validate + Suite-Run grün stellen
