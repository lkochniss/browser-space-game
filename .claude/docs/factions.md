# Factions

## Zweck (T-073)

NPC-Faktionen mit Player-Reputation. Foundation für PvE-Encounter (T-074),
Trade-Modifier (T-110+), Diplomatie (T-130+).

## Faction-Entity

| Field | Beschreibung |
|-------|--------------|
| `id` (FactionId) | UUID |
| `slug` | Eindeutiger Lookup-Key (z.B. `core_imperium`, `frontier_clans`) |
| `name` | Display-Name |
| `description` | Lore-Text |

## FactionSeedService

Idempotenter Seed-Service. Wird gerufen von:
- `IntegrationTestCase::setUp` — vor jedem IT-Test
- `InteractiveDemoCommand::setupSession` — beim ersten Demo-Run / Reset
- (Später: T-044 Bootstrap, Production-Setup)

`seed()` legt fixe Faction-Set an, falls noch nicht vorhanden.

## PlayerFactionReputation-Entity

| Field | Beschreibung |
|-------|--------------|
| `id` | UUID |
| `player_id` (FK) | |
| `faction_id` (FK) | |
| `score` | int (kann negativ); typischer Range -1000 .. +1000 |

UNIQUE(player_id, faction_id) — pro Pair max 1 Reputations-Eintrag.

## ReputationService

Foundation-Operationen: `addScore`, `setScore`, `getOrCreate(player, faction)`.
Effekte (Discounts, Quest-Locks) kommen mit den jeweiligen Folge-Tickets.

## Files

- `src/Faction/Model/{Faction,PlayerFactionReputation}.php`
- `src/Faction/Repository/{FactionRepository,PlayerFactionReputationRepository}.php`
- `src/Faction/Service/{FactionSeedService,ReputationService}.php`
- `src/Common/Doctrine/Type/FactionIdType.php`

## Status

Foundation. Aktuell **nicht aktiv konsumiert** in Game-Logic. Bereit für:

- **T-074** Pirate-Encounter-Spawn (Hostile-Faction-Threshold)
- **T-110+** Trade-Routes mit Faction-Discount
- **T-130** Alliance-Treaties (Faction-Reputation als Voraussetzung)
- **T-099** Threat-Skalierung (Reputations-basierte Difficulty)
