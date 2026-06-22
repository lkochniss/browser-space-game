# T-023b: Station-Maintenance + Übernahme-Mechanik

**Type:** Feature
**Status:** Draft
**Effort:** L (TBD)
**Depends on:** T-023 (Raumstation Foundation), T-005 (Population Tick-Logic)
**Blocks:** —

## Beschreibung

Folge-Ticket zu T-023. User-Vision: Stationen sind **Raumdocks mit großem
Inventar, ohne Resource-Produktion**. Maintenance ist bewusst erschwert.

Wenn Maintenance-Kosten nicht gedeckt werden, stirbt die Pop auf der Station,
Status wechselt zu ABANDONED — andere Player können dann übernehmen.

### Lore-Context (40k-Style)

Stations sind **nicht baubar** — die Technologie ist im Universum verschollen.
Galaxy-Initial-Spawn (Folge T-175) verteilt existierende Stations, teils
Pirate-owned. Player können nur über ABANDONED-Claim oder Combat-Capture
(Folge T-176) übernehmen. Dieses Ticket (T-023b) deckt die Maintenance- und
ABANDONED-Mechanik ab; Build-Path-Deprecation und Pirate-Spawn-Logik liegen
in Folge-Tickets T-174/T-175/T-176.

## Acceptance Criteria

### Maintenance-Tick
- [ ] TBD: `StationMaintenanceProcessor` (Tick-Service, nicht TickProcessorInterface,
  da Station nicht Planet-bezogen ist — globaler Service ähnlich `FleetArrivalService`)
- [ ] TBD: Pro Station mit Status=ACTIVE: Verbrauch von W/F/O × `populationOnStation`
  pro Tick (analog T-005 PopulationConsumptionProcessor)
- [ ] TBD: Resource-Source: Station-Storage (kann nichts produzieren!) — wenn leer,
  Pop stirbt
- [ ] TBD: Pop-Mortality bei Mangel: analog T-005 (freie-first-Pattern, aber
  Station hat nur "free" Pop, kein assigned)
- [ ] TBD: Bei `populationOnStation == 0`: Status → ABANDONED, owner=null

### ABANDONED-State + Übernahme
- [ ] TBD: `ClaimAbandonedStationCommand` (playerId, stationPoiId)
- [ ] TBD: Validation: Station status = ABANDONED, Player hat Shipyard L3 im
  selben System (analog T-023 Build-Gate)
- [ ] TBD: Effekt: owner=Player, status=ACTIVE, populationOnStation=initial-200
  (vom Heimat-Planet abgezogen analog T-023)
- [ ] TBD: Storage bleibt erhalten (Loot-Bonus für Übernehmer)

### Maintenance-Refill
- [ ] TBD: Player kann via T-015b Cargo-Transfer Resources zur Station bringen
  → Station-Storage wird wieder gefüllt → Maintenance-Tick zieht aus diesem Storage

## Resolved Decisions

- **Tick-Frequenz:** Globaler `StationMaintenanceService` im TickEngine-Run
  (analog `FleetArrivalService`). Kein eigener Cron, kein Player-Tick.
- **Verbrauch pro Pop:** ×1.5 Multi vs. Planet-Pop (Klima-Schwierigkeit-
  Reflektion, passt zu Vision "Maintenance bewusst erschwert").
- **Storage-Fluss:** Voll-bidirektional — Player kann jederzeit Resources
  hin- und herbewegen (symmetrisch zu T-015b). Station = flexibles Warehouse.
- **Abandoned-Cleanup:** Nicht despawnen — ABANDONED-Stations bleiben ewig
  als POI auf der Map. Pirate-Takeover-Mechanik (T-176) kann sie reaktivieren.

## Notes

- Foundation für strategische Tiefe: Stations-Halt ist Long-Term-Engagement-
  Mechanik
- Synergie mit T-093 Allianz-Stationen — Allianz-Members teilen Maintenance-Last
- Kein Anti-Crush-Konflikt: Spieler kann immer eine Station "fallen lassen", ist
  kein Heimat-Verlust
