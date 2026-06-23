# T-087 Fog-of-War / Discovery-State

**Type:** Feature
**Epic:** Exploration & Probes
**Domain:** Probe
**Blocked By:** T-018, T-007, T-013, T-019
**Status:** Draft
**Effort:** L
**Depends on:** T-018 (Teleskop), T-007 (SolarSystem), T-013 (Sonden), T-019 (POI-Foundation)
**Blocks:** T-160 (Galaxy-Map)

## Beschreibung

Spieler-spezifische Sichtbarkeit: nicht erkundete Systems sind verborgen.
Erkundung via Teleskop (Meta-Discovery) + Sonden (Detail-Scan).

Drei Discovery-Levels pro System pro Spieler:
1. **UNKNOWN**: nicht sichtbar auf Map
2. **METADATA**: Position + Existenz-Marker sichtbar (Teleskop-Discovery)
3. **SCANNED**: Planeten + Resources + POIs sichtbar (Sonde nötig)

## Existing-POI-Subtypes (Stand T-023)

T-019/T-020/T-022/T-085/T-023 sind done. T-087 muss alle existing Subtypes
scopen für Discovery-Visibility:
- AsteroidField (T-020) — sichtbar bei SCANNED
- Nebula (T-022) — sichtbar bei METADATA, Inhalt erst bei SCANNED durch eigene Fleet
- Wormhole (T-085) — sichtbar bei METADATA wenn Tech-Level erforscht (T-026)
- SpaceStation (T-023) — sichtbar bei SCANNED (Owner zeigt seine Stationen)

Future Subtypes (T-021, T-074-T-077, T-086):
- DebrisField, UnknownFleet, RenegadeOutpost, BlackHole — alle bei SCANNED

## Acceptance Criteria

- [ ] `PlayerSystemDiscovery`-Entity (playerId, systemId, level, discoveredAt)
- [ ] Lazy-default: ohne Row = UNKNOWN
- [ ] Teleskop-Building (T-018) erweitert Discovery-Range pro Level
- [ ] Probe (T-013) upgradet System auf SCANNED
- [ ] **POI-Visibility-Filter** pro Subtype (siehe oben — Subtype-Map mit
  `requiredDiscoveryLevel`)
- [ ] Galaxy-Map (T-160) rendert nur sichtbare Systems + erlaubte POIs
- [ ] Other-Player-Activity: Andere Spieler-Schiffe nur sichtbar wenn System
  SCANNED + aktiv im Sensor-Range (T-068)

## Affected Tests
- tests/Discovery/Service/SystemDiscoveryTest.php (level-Übergänge)
- tests/Discovery/Service/MapVisibilityTest.php (POI-Filter pro Level)

## Fixtures Needed
Yes — Player + diverse Discovery-States + POI-Mix

## Notes
- Galaxy-State ist persistent + global — Fog-of-War nur visuell pro Spieler
- Allianz-Discovery-Sharing: optional Folge-Ticket (Allianz-Members teilen
  Discovery automatisch)
- Owner-Bias: eigene Stationen (T-023) sind immer sichtbar (Owner-Override)
