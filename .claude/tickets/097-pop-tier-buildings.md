# T-097 Pop-Tier-Buildings (Genebank / Cloning-Vat / Civic-Center)

**Type:** Feature
**Status:** Draft
**Effort:** M (TBD)
**Depends on:** T-005 (Pop), T-070 (QoL), T-089 (Civilian-Goods), T-090 (Medicine)
**Blocks:** —

## Beschreibung

Erweitert Pop-Mechanik um spezialisierte Buildings die nicht in T-070 QoL-Foundation passen.

Neue Buildings:
- GENEBANK: Pop-Wachstum-Boost (×1.2/lvl) durch genetische Vielfalt; nutzt Biomass (T-090)
- CLONING_VAT: Sofort-Pop-Spawn (kostet viel Biomass + Pharma-Base, große Pop-Erzeugung in einem Schub)
- CIVIC_CENTER: Pop-Tier-Progression-Speed-Boost (Working→Middle→Upper schneller)
- AGRI_DOME: Food-Production-Building (statt nur HUB-default), höherer Output auf TROPICAL/OCEAN
- WATER_RECLAIMER: Water-Production-Building (Tech-gated, höherer Output auf ICE)
- ATMOSPHERIC_PROCESSOR: Oxygen-Production-Building (universal, höherer Output via Forschung)

## Acceptance Criteria

- [ ] TBD: Neue BuildingType-Werte
- [ ] TBD: Pop-Wachstum-Multiplier-Integration im PopulationGrowthService (T-005)
- [ ] TBD: Cloning-Vat als Single-Use-Cost-Action (Resources für Pop-Schub)
- [ ] TBD: Renewable-Production-Buildings als Erweiterung (Default-Renewable-Production via HUB bleibt, aber dedicated Buildings skalieren)

## Open Questions

- Cloning-Vat-Cooldown? Anti-Spam durch Resource-Cost reicht?
- AGRI_DOME ersetzt HUB-Food-Default oder additiv?
- Genebank-Effekt mit Cybernetic-Implant (T-090) stackable?

## Notes

- Vertieft Pop-Mechanik strukturell — Pop wird strategisch managed, nicht nur passiv
- Wirtschafts-Hebel: BIOMASS wird Pop-Building-Verbrauchsgut → Industrie-Trade-Wert
