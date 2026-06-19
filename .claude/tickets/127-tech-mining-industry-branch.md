# T-127 Forschungs-Branch: Mining / Industrie

**Type:** Feature
**Status:** Draft
**Effort:** L (TBD)
**Depends on:** T-025 (Forschungs-Framework), T-002, T-067, T-115
**Blocks:** —

## Beschreibung

Tech-Branch für Industrie-Specialist-Track (T-098). Skaliert Mining + Refining + Tier-2/3-Production.

Tech-Tree (Tier 1-5):

**Tier 1**: Improved-Drilling-Bits (+10% Mining-Output), Smelter-Efficiency (Refining-Cost -10%)
**Tier 2**: Deep-Drill-Tech (Voraussetzung für T-108 Deep-Drill-Building), Auto-Smelter (idle-Smelter-Output +5%)
**Tier 3**: Composite-Materialien (T-067 Composite-Building schneller), Mining-Drone-Network (Asteroid-Hub +20%)
**Tier 4**: Plasteel-Forging (Voraussetzung Plasteel-Forge, T-115), Recycling-Systems (Refining gibt 5% Resources zurück)
**Tier 5**: Adamantium-Crucible (Voraussetzung Adamantium-Production), Quantum-Mining (Anti-Matter-Harvest aus Black-Hole +50% T-086)

## Acceptance Criteria

- [ ] TBD: 10 ResearchNode-Definitionen für Tier 1-5 (2 pro Tier)
- [ ] TBD: Effekt-Resolver-Integration (Mining/Refining-Multiplier)
- [ ] TBD: Building-Unlock-Gates (Tier 2 → Deep-Drill, Tier 4 → Plasteel-Forge, Tier 5 → Adamantium)
- [ ] TBD: Branch-Lock konsistent mit T-098 (Industry-Track unlockt Tier 4-5 voll, andere nur Tier 1-3)

## Open Questions

- 10 Nodes pro Branch ausreichend Tiefe oder mehr (15-20)?
- Tech-Cost-Skalierung pro Tier?
- Synergien mit Specialist-Track (T-098 +30% wirkt zusätzlich auf diese Boni)?

## Notes

- Industrie-Track wird via dieser Branch zur wirtschaftlichen Powerhouse-Identität
- Verbindet alle Resource/Refining/Mining-Tickets in einem Tech-Pfad
