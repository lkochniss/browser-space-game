# T-018: Teleskop + System-Erkundung

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-007

## Description

`docs/Teleskop.md` + `docs/Erkundung.md`: Teleskop = Building auf Planet. Reichweite-basiert: entdeckt Sterne (zeigt Sonnensysteme an) + im eigenen System Planeten + POIs. Meta-Ebene Erkundung. Detail-Erkundung weiterhin per Sonde/Schiff (T-013).

## AC

- [ ] `BuildingType::TELESCOPE`
- [ ] Reichweite per Level (Light-Years o.ä.)
- [ ] `TelescopeDiscoveryProcessor` (TickProcessor) entdeckt unbekannte Systeme im Radius
- [ ] Discovered-State persistiert pro Player auf SolarSystem (sehbar = entdeckt, nicht-erkundet)
- [ ] Im eigenen System: Teleskop deckt Planeten + POIs nach Schwierigkeit auf
- [ ] POI-Schwierigkeit + Scanner-Forschungs-Level (T-025) entscheiden Aufdeckung

## Affected

- `src/Building/ValueObject/BuildingType.php`
- Neu: `src/Tick/Processor/TelescopeDiscoveryProcessor.php`
- Neu: per-Player Discovery-State (z.B. `Player::discoveredSystems`)

## Open Questions

1. Range pro Level Formel?
2. Discovery-Tick-Frequenz: jeder Tick voll scannen oder eine probabilistische Chance?
3. Eigene Domain `Discovery/` oder Teil von SolarSystem/Player?
