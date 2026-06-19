# T-116 Mega-Strukturen Foundation (Genesis-Forge first)

**Type:** Feature
**Status:** Draft
**Effort:** XL
**Depends on:** T-115 (Tier-3), T-027 (Planetologie), T-052 (Allianz)
**Blocks:** —

## Beschreibung
Allianz-built oder mehrere-Spieler-built Mega-Strukturen. Endgame-Content, Wochen bis Monate Build-Time, Galaxy-Effekt.

Erste Mega-Struktur: **Genesis-Forge** — terraformt einen Planeten zu beliebigem Planet-Type um. Verbindet zu T-027 Planetologie-Forschung.

Folge-Tickets: Dyson-Sphäre, Wormhole-Generator, Stargate.

## Acceptance Criteria
- [ ] MegaStructure-Entity (id, type=GENESIS_FORGE, location=System, ownerType: PLAYER|ALLIANCE, ownerId, contributors-Map, status, hp, completedAt)
- [ ] Genesis-Forge-Cost: 50k Plasteel + 20k Adamantium + 10k AI-Core + 100k Pop + 6 Wochen Build-Time
- [ ] Multi-Player-Donation: ähnlich Allianz-Station (T-093)
- [ ] Aktivierung: Trigger-Aktion → wählt Target-Planet → wählt neuen PlanetType → Terraform-Process startet (4 Wochen)
- [ ] Forschungs-Voraussetzung: Tier-5 Planetologie (T-027)
- [ ] Pro Mega-Struktur: 1 Use, dann zerstört (single-use für Anti-Repeat)
- [ ] Genesis-Forge zerstörbar im Battle (T-103) → wenn fällt, Build verloren

## Affected Tests
- tests/MegaStructure/Service/GenesisForgeBuildTest.php
- tests/MegaStructure/Service/TerraformProcessTest.php

## Fixtures Needed
Yes — Allianz mit accumulierten Resources

## Notes
- Single-Use-Design vermeidet OP-Stacking (sonst eine Allianz terraformt alle Planeten)
- Folge-Mega-Strukturen (Dyson, Wormhole, Stargate) jeweils eigene Tickets
