# T-107 Manufacturing-Building-Cluster (Munition / Civilian / Bio / Tier-3)

**Type:** Feature
**Epic:** Resources Tier-2/3
**Domain:** Building
**Blocked By:** T-067, T-088, T-089, T-090, T-091, T-115
**Status:** Draft
**Effort:** XL (TBD)
**Depends on:** T-067 (Tier-2), T-088 (Munition), T-089 (Civilian), T-090 (Medicine), T-091 (Tier-3-Combat), T-115 (Tier-3-Resources)
**Blocks:** —

## Beschreibung

Bündel-Ticket für alle Manufacturing-Buildings die in T-088, T-089, T-090, T-091 als TBD genannt sind. Vermeidet ~20 Mini-Tickets für jedes Manufacturing-Building.

Buildings (gruppiert):

**Munition (T-088):**
- AMMO_FACTORY (BALLISTIC_AMMO)
- WARHEAD_PLANT (WARHEAD)
- PLASMA_FORGE (PLASMA_CHARGE — überschneidet T-115 Plasma-Forge?)

**Civilian (T-089):**
- TEXTILE_MILL
- CERAMIC_KILN
- ELECTRONICS_PLANT
- LUXURY_ATELIER
- HOLO_STUDIO

**Bio/Medicine (T-090):**
- BIO_LAB (BIOMASS-Boost)
- PHARMA_PLANT
- VACCINE_FACILITY
- CYBERNETICS_CLINIC

**Tier-3-Combat (T-091):**
- TARGETING_LAB
- ARMOR_FORGE
- ECM_LAB
- WARP_FOUNDRY

**Tier-3-Resources (T-115):**
- PLASTEEL_FORGE
- ADAMANTIUM_CRUCIBLE
- AI_FOUNDRY (existiert in T-115)

## Acceptance Criteria

- [ ] TBD: Alle BuildingType-Werte
- [ ] TBD: Pro Building Recipe-Hook im RefinementProcessor
- [ ] TBD: Pop-Bedarf + Power-Consumption pro Tier konsistent
- [ ] TBD: Build-Cost-Skalierung (Tier-1 günstig, Tier-3 enorm)
- [ ] TBD: Forschungs-Lock pro Building (Tier-2-Buildings = Tier-3-Forschung etc.)

## Open Questions

- Splitting in mehrere Tickets sinnvoll oder als Mega-Ticket abwickeln?
- Storage für jeweilige Outputs eigene Tickets oder integriert?
- Effort XL — eventuell pro Cluster (Munition/Civilian/Bio/Combat/Tier-3) eigenes Ticket bei Implementation?

## Notes

- ~20 Buildings — größtes Single-Building-Ticket. Bei Implementation in Phasen splitten.
- Reduziert Ticket-Overhead auf Konzept-Ebene; Implementation kann phasenweise gehen
- Forschung-Locks bringen sinnvolle Pacing
