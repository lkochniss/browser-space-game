# T-090 Medicine / Bio-Resources

**Type:** Feature
**Status:** Draft
**Effort:** M (TBD)
**Depends on:** T-005 (Pop-Verbrauch), T-070 (Hospital)
**Blocks:** —

## Beschreibung

Bio-Resources für medizinische Versorgung. Hospital (T-070) wird vom abstrakten "+ Pop-Cap"-Building zu konkretem Medicine-Consumer mit Effekt-Skalierung.

Neue Resources:
- BIOMASS (RAW; aus FOOD-Surplus + Pop-Tier)
- PHARMA_BASE (REFINED; aus Biomass + Chip)
- VACCINE (REFINED; Pandemie-Defense — siehe Galaxy-Events T-076)
- CYBERNETIC_IMPLANT (Tier-3; aus AI-Core + Pharma-Base)

Effekte:
- Hospital konsumiert Pharma-Base/Tick → reduziert Pop-Death-Rate bei W/F/O-Mangel
- Vaccine schützt vor Galaxy-Event-Pandemien
- Cybernetic-Implant: optional Pop-Boost (höheres Output, aber teuer)

## Acceptance Criteria

- [ ] TBD: Neue ResourceTypes + Recipes
- [ ] TBD: Manufacturing-Buildings: BIO_LAB, PHARMA_PLANT, VACCINE_FACILITY, CYBERNETICS_CLINIC
- [ ] TBD: Hospital (T-070) konsumiert Pharma-Base, Effekt skaliert mit Verfügbarkeit
- [ ] TBD: Pandemie-Galaxy-Event (T-076 Erweiterung): Pop-Death falls keine Vaccines

## Open Questions

- Cybernetic-Implant als Pop-Tier-Upgrade oder separate Mechanik?
- Pandemie-Frequenz (alle X Wochen)?
- Pharma-Base auch als Combat-Heal-Resource für Schiff-Crew (T-105)?

## Notes

- Pop-Health-Komponente erweitert Survival-Layer
- Verstärkt Diplomatie-Wert: Vaccine-Trade während Pandemie ist Coop-Hebel
