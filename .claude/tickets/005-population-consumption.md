# T-005: Population-Verbrauch pro Tick

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-001, T-004

## Description

`docs/Bevölkerung.md`: Pro Tick verbraucht Pop Wasser + Nahrung (Sauerstoff je nach Planet-Typ). Bei Mangel: Wachstum pausiert, dann sterben Menschen. Reihenfolge: zuerst freie Pop reduzieren, dann assigned (Building-Leistung). Bei Überschuss: zuerst assigned auffüllen, dann freie wachsen lassen.

## AC

- [ ] Neuer `PopulationConsumptionProcessor` (`TickProcessorInterface`)
- [ ] Pro Tick: `pop.total * waterPerCapita` aus Wasser-Resource ziehen
- [ ] Pro Tick: `pop.total * foodPerCapita` aus Nahrung-Resource ziehen
- [ ] Bei Mangel: Wachstum stoppt; weiterer Mangel → kill, freie zuerst
- [ ] Bei Überschuss: grow bis cap; assigned vorher auffüllen
- [ ] Sauerstoff-Verbrauch nur bei Planet-Typen ohne Atmosphäre (T-008)

## Affected

- Neu: `src/Tick/Processor/PopulationConsumptionProcessor.php`
- `src/Tick/Engine/TickEngine.php` (Reihenfolge: Production VOR Consumption)
- `src/Resource/Service/ResourceProductionConfig.php` (per-capita Konstanten)

## Open Questions

1. Per-capita-Werte? Vorschlag: 0.1 W/Pop/Tick, 0.1 N/Pop/Tick.
2. Reihenfolge Processors fix oder konfigurierbar?
3. Wachstumsformel? Linear (z.B. +1%/Tick bis Cap) oder logistisch?
