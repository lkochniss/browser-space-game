# T-085 Wurmloch-POI

**Type:** Feature
**Status:** Draft
**Effort:** M
**Depends on:** T-019 (POI), T-026 (Antriebs-Tree)
**Blocks:** —

## Beschreibung
Wormhole-POI ermöglicht schnelle Long-Distance-Bewegung zwischen 2 Systems. Voraussetzung: Schiff hat Warp-Drive-Tech (T-026 FTL-Tier-2).

Wurmlöcher sind statisch: A↔B Pairs in Galaxy. Zugang verbraucht Treibstoff hoch + dauert Round-Trip-Cooldown.

## Acceptance Criteria
- [ ] WormholePOI-Entity (systemA, systemB, requiredTechId, fuelMultiplier, cooldownHours)
- [ ] Galaxy-Init seedet 3-5 Wurmloch-Paare zwischen entfernten Systems
- [ ] Schiff-Movement-Service: Wurmloch-Route nutzbar wenn Tech vorhanden
- [ ] Wurmloch-Transit verbraucht 5× Standard-Treibstoff
- [ ] Per Schiff Cooldown 24h nach Transit (kein Spam)
- [ ] Wurmloch sichtbar auf Galaxy-Map (T-160) mit Tech-Lock-Indikator
- [ ] Discovery: Wurmloch erst sichtbar nach Sondierung (T-018 Teleskop)

## Affected Tests
- tests/Movement/Service/WormholeRouteTest.php
- tests/Movement/Service/WormholeFuelCostTest.php

## Fixtures Needed
Yes — pre-seeded Wormhole-Pairs

## Notes
- Wurmlöcher sind kein Allianz-Privileg, alle Spieler nutzen gleiche
- Eventuell instabile Wurmlöcher als Galaxy-Event (T-076) — Folge-Idee
