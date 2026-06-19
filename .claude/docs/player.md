# Player + Galaxy-Bootstrap

## Player-Aggregat

```
Player
 ├ id (PlayerId, UUID)
 └ planets: Collection<Planet>   (1:N, Player.claimPlanet)
```

Player ist Aggregat-Wurzel für Planeten. Resources/Buildings/Pop hängen am
Planet, nicht am Player.

## Bootstrap: ClaimStartPlanet (T-007 + T-008 + T-085 + T-018)

`ClaimStartPlanetCommand(playerId, planetId)` → `ClaimStartPlanetCommandService`:

1. **Player anlegen** + claim Start-Planet (TERRAN/MEDIUM, hardcoded für Onboarding)
2. **Start-Planet seeden** (T-001/T-002): 100 W/F/O initial, 50 Pop, IRON_ORE-Deposit 1000
3. **Galaxy generieren** (T-007): 5 SolarSystems
   - System 0 = Start-System (enthält Start-Planet)
   - System 1-4 = random PlanetType+Size, eigener seedRandomPlanet
4. **POIs spawnen**:
   - **AsteroidFields** (T-020): 0-2 pro System, 1-3 FINITE-Resources, 500-2000 Amount
   - **Nebulae** (T-022): 30% Chance pro System, Concealment 3-9
   - **Wormhole-Pair** (T-085): 1 Paar zwischen 2 random Systems, FTL-Tier-2-Lock
5. **Persistieren** (`em->flush`)
6. **T-018**: `TelescopeDiscoveryService::markDiscovered($player, $homeSystem)` — Heimat-System sofort entdeckt

## Files

- `src/Player/Model/Player.php` (Entity)
- `src/Player/ValueObject/PlayerId.php`
- `src/Player/Repository/PlayerRepository.php`
- `src/Player/Command/{CreateNewPlayerCommand,...}.php`
- `src/Player/Service/{CreateNewPlayerService,...}.php`
- `src/Planet/Service/ClaimStartPlanetCommandService.php` (zentral, da Galaxy + Player + Planet zusammen)
- `src/Common/Doctrine/Type/PlayerIdType.php`

## Cross-Domain

- **Planet**: 1:N Ownership; Planet.player ManyToOne
- **SolarSystem**: Galaxy-Container, Foundation-Layer
- **POI**: alle POIs hängen an SolarSystem aus Galaxy-Init
- **Discovery (T-018)**: Initial-Hook für Heimat-System
- **Faction (T-073)**: Reputation-Anker, aktuell nicht aktiv genutzt

## Galaxy-Konstanten

| Konstante | Wert | Quelle |
|-----------|------|--------|
| `START_GALAXY_SYSTEM_COUNT` | 5 | T-007 |
| `START_POPULATION` | 50 | T-004 |
| `RENEWABLE_START_AMOUNT` | 100 W/F/O | T-001 |
| `START_IRON_DEPOSIT` | 1000 | T-002 |
| `ASTEROID_FIELD_MAX_PER_SYSTEM` | 2 | T-020 |
| `NEBULA_SPAWN_CHANCE_PERCENT` | 30 | T-022 |
| `WORMHOLE_PAIRS_PER_GALAXY` | 1 | T-085 |

## Geplant

- **T-036** User-Entity (separater Account-Layer; Player wird Spielfigur)
- **T-043** User-vs-Player-Trennung
- **T-101** Planet-Cap pro Player (max 5, erweiterbar via Forschung)
- **T-122** Player-Background (5 Backgrounds, +5%/-2% Identity-Boni)
