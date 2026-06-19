# T-087 Fog-of-War / Discovery-State

**Type:** Feature
**Status:** Draft
**Effort:** L
**Depends on:** T-018 (Teleskop), T-007 (SolarSystem), T-013 (Sonden)
**Blocks:** T-160 (Galaxy-Map)

## Beschreibung
Spieler-spezifische Sichtbarkeit: nicht erkundete Systems sind verborgen. Erkundung via Teleskop (Meta-Discovery) + Sonden (Detail-Scan).

Drei Discovery-Levels pro System pro Spieler:
1. UNKNOWN: nicht sichtbar auf Map
2. METADATA: Position + Faktor sichtbar (Teleskop-Discovery)
3. SCANNED: Planeten + Resources + POIs sichtbar (Sonde nötig)

## Acceptance Criteria
- [ ] PlayerSystemDiscovery-Entity (playerId, systemId, level: UNKNOWN/METADATA/SCANNED, discoveredAt)
- [ ] Lazy-default: ohne Row = UNKNOWN
- [ ] Teleskop-Building (T-018) erweitert Discovery-Range pro Level
- [ ] Probe (T-013) upgradet System auf SCANNED
- [ ] Galaxy-Map (T-160) rendert nur sichtbare Systems
- [ ] Other-Player-Activity: Andere Spieler-Schiffe nur sichtbar wenn System SCANNED + aktiv im Sensor-Range
- [ ] POIs (Wormhole T-085, Black-Hole T-086, Outposts T-075) sichtbar erst nach SCANNED

## Affected Tests
- tests/Discovery/Service/SystemDiscoveryTest.php (level-Übergänge)
- tests/Discovery/Service/MapVisibilityTest.php

## Fixtures Needed
Yes — Player + diverse Discovery-States

## Notes
- Galaxy-State ist persistent + global — Fog-of-War nur visuell pro Spieler
- Allianz-Discovery-Sharing: optional Folge-Ticket (Allianz-Members teilen Discovery automatisch)
