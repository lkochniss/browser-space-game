# T-089 Luxury / Civilian-Goods (Pop-Demand-Layer)

**Type:** Feature
**Status:** Draft
**Effort:** M (TBD)
**Depends on:** T-005 (Pop-Verbrauch), T-067 (Tier-2)
**Blocks:** —

## Beschreibung

Pop verbraucht aktuell nur W/F/O. Erweiterung: Civilian-Goods für Pop-Wachstum-Boost + Loyalty + Wirtschaftliche Tiefe.

Neue Resources (REFINED + RAW):
- TEXTILES (aus Plastic-Resin + Pop-Labor; Pop-Comfort)
- CERAMIC_GOODS (aus Silicon + Aluminum-Bar; Civilian-Pop-Demand)
- CONSUMER_ELECTRONICS (aus Chip + Plastic-Resin; höhere Pop-Tier)
- LUXURY_GOODS (aus Multi-Refined-Inputs; höchster Trade-Wert)
- ENTERTAINMENT_HOLO (aus Chip + Pop-Labor; Pop-Loyalty-Boost)

Effekt:
- Pop-Tier-System: Working-Class (W/F/O reicht), Middle-Class (zusätzlich Textiles/Ceramic), Upper-Class (Electronics/Luxury)
- Höherer Pop-Tier = höheres Wachstum, höheres RP-Output via RESEARCH_LAB (T-025)
- Mangel an Civilian-Goods → Pop-Tier degradiert, Wachstum stagniert

## Acceptance Criteria

- [ ] TBD: Neue ResourceTypes inkl. Recipes
- [ ] TBD: Pop-Tier-Mechanik (Working/Middle/Upper) als Embeddable-VO auf Population
- [ ] TBD: Tier-Progression-Service (basiert auf Civilian-Goods-Verfügbarkeit pro Tick)
- [ ] TBD: Manufacturing-Buildings: TEXTILE_MILL, CERAMIC_KILN, ELECTRONICS_PLANT, LUXURY_ATELIER, HOLO_STUDIO
- [ ] TBD: T-005 PopulationConsumptionProcessor erweitert um Civilian-Demand

## Open Questions

- Pop-Tier-Anteil: alle Pop gleichzeitig im Tier oder gestaffelt?
- Tier-Degradation-Geschwindigkeit (sofort oder über Wochen)?
- Konflikt mit T-122 Player-Background (Imperialer Adel hat Luxus-Vorteile)?

## Notes

- Wirtschaftliche Vertikal-Integration: Industrie-Spieler verkauft Civilian-Goods, Luxus-Region (T-118) zahlt Premium
- Pop-Tier macht Pop nicht nur "Zahl" sondern qualitatives Asset
- Verstärkt Trade-Sinnhaftigkeit (T-110/T-111)
