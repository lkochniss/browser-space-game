# Discovery (Galaxy Fog-of-War Foundation)

## Zweck (T-018)

Player kennt nur Systeme, die er entdeckt hat. Heimat-System ist initial
discovered; alle anderen sind unsichtbar bis Telescope sie aufdeckt.
Foundation für T-087 Fog-of-War (Tier-Levels + POI-Discovery folgen dort).

## PlayerSystemDiscovery-Entity

```
id (DiscoveryId)
player_id (FK)
solar_system_id (FK)
discovered_at (datetime_immutable)

UNIQUE(player_id, solar_system_id)
```

Existence des Eintrags = "discovered". Boolean-Marker, keine Tier-Levels in
T-018-Foundation.

## TelescopeDiscoveryService

| Method | Zweck |
|--------|-------|
| `markDiscovered(player, system)` | Idempotent. Genutzt von ClaimStartPlanet für Heimat-System |
| `runTickForPlayer(player): int` | Pro Tick reveals N=`Σ telescopeLevel` random unseen Systems via Fisher-Yates × Randomizer |

`Planet::getTelescopeLevel($now)` aggregiert max-Level aller fertigen TELESCOPE-
Buildings auf dem Planeten. Service summiert über alle Player-Planets.

## TELESCOPE-Building (T-018)

Cost: 150 IRON_ORE + 200 SILICON + 100 COPPER + 10 pop
Duration: 45min × 2^level (T-062 Skalierung)

## Initial-Discovery

`ClaimStartPlanetCommandService` callt `markDiscovered($player, $homeSystem)`
nach Galaxy-Generation + flush. Demo-CLI sieht nach Reset nur 1 entdecktes
System (Heimat); Galaxy-Overview zeigt Counter unbekannter.

## Demo-Integration

- **Galaxy Overview**: filtert auf entdeckte Systeme + zeigt `<n unbekannte System(e)>`
- **Tick Forward**: callt `runTickForPlayer` → Output `Discovered: N`

## Files

- `src/Discovery/Model/PlayerSystemDiscovery.php`
- `src/Discovery/Repository/PlayerSystemDiscoveryRepository.php` (`findByPlayer`, `isDiscovered`)
- `src/Discovery/Service/TelescopeDiscoveryService.php`
- `src/Discovery/ValueObject/DiscoveryId.php`
- `src/Common/Doctrine/Type/DiscoveryIdType.php`

## Cross-Domain

- **Building/TELESCOPE**: Source für Discovery-Reveals
- **Player + SolarSystem**: FKs auf Discovery-Entity
- **ClaimStartPlanet**: Initial-Hook für Heimat-System
- **Demo-CLI**: Galaxy-Overview-Filter

## Geplant

- **T-087 Fog-of-War**: Tier-Levels (Sichtbarkeit-Quality) + POI-Discovery
- **T-027 Planetologie**: Probe-Boost zur Discovery
- **T-018b** Probe-basiertes Discovery (Out-of-scope von T-018)
