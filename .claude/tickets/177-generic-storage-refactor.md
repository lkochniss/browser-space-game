# T-177: Generic-Storage-Refactor (Planet)

**Type:** Feature (Refactor)
**Status:** Done
**Effort:** L (~6-8h, hoch wegen Migration + Test-Refactor)
**Depends on:** T-180 (Size-Multiplier-Config, Done)
**Blocks:** T-066 (Fuel-Storage), T-178 (Ship-Cargo), T-179 (Pop-Storage), T-183 (Station-Generic-Storage)
**Supersedes:** T-061 (per-Resource-Cap durch Volume-Cap ersetzt)

## Beschreibung

Storage-Vision-Pivot: Statt 6 dedizierter Storage-Buildings pro Resource-
Kategorie hat jeder Planet **einen einheitlichen Storage** mit Volumen-Cap.
Alle Items (Resources, Erzeugnisse, Pop) belegen Volumen abhängig von ihrem
**Size-Multiplier** (T-180).

T-061 (Done) wird damit refactored — keine Category-Buckets mehr.

### Lore / Intuition

- Wasser braucht mehr Lager-Volumen als Wasserstoff (Aggregatzustand)
- Erze sind schwer und voluminös pro Einheit
- Pop ist nicht stapelbar → massiver Size-Multi
- Storage ist Lager-Halle, kein Resource-Lock-Pattern

## Open Questions

### Q1: Migration bestehender Storage-Buildings (T-061) — RESOLVED = (a)

**Decision:** Konsolidieren in **1 generisches "Warehouse"-Building**.
Die anderen 5 T-061-Storage-Buildings werden gelöscht. Hauptquelle für
Storage-Volume ist dieses neue Warehouse.

### Q2: Base-Storage-Volume ohne Storage-Building — RESOLVED = (c)

**Decision:** **Skaliert mit HQ-Level** (T-172). HQ pro Level etwas mehr
Platz, aber der meiste Volume kommt durch das neue Warehouse-Building (Q1=a).
Konkrete Werte: HQ-Beitrag klein (z.B. 50 m³ Base + 25 m³/Level), Warehouse
liefert den Großteil (z.B. 500 m³/Level).

### Q3: Overflow-Verhalten bei Volume-Cap — RESOLVED = (a)

**Decision:** Volume-Cap-Stop für ALLE Production (analog T-061-Pattern).
Mines/Refineries/Renewables pausieren wenn Volume voll. Konsistent + leicht
zu reasoning. Spillover/Prio/Auto-Eject (b/c/d) bleiben offen für Folge
(z.B. T-110 Auto-Trade-Routes als Pressure-Valve).

### Q4: Storage-Read/Write-API — RESOLVED = (a)

**Decision:** Volume-aware-Operations direkt am Planet-Aggregate:
`canAddItem(type, qty)`, `maxAddableQuantity(type, qty)`. Kein neuer
StorageService. Production-Processors nutzen Legacy-Shim `getStorageCapacity(R)`
(liefert `current + maxAddable`) — neue Caller direkt `maxAddableQuantity()`.

### Q5: Display / UI-Logic — RESOLVED = (a)

**Decision:** Volume-Display als Gesamtwert + per-Resource-Quantity-Liste.
Demo-CLI Status zeigt `Volume: 850/5000 m³`; Volume-Beitrag pro Resource
via `ResourceVolumeConfig::getMultiForResource(R)` ableitbar.

## Acceptance Criteria

- [x] `Planet::getStorageVolumeCapacity(): int` (live-computed:
      `BASE_VOLUME_CAPACITY` + Σ Building.getVolumeContribution × Level)
- [x] `Planet::getStorageVolumeUsed(): int` (Σ resources × ResourceVolumeConfig + Pop × 10)
- [x] `Planet::getStorageVolumeFree()` / `canAddItem()` / `maxAddableQuantity()`
- [x] `BuildingType::getVolumeContribution(): int` ersetzt
      `getStorageContribution(ResourceType)`
- [x] Storage-Building-Familie konsolidiert: 6 T-061-Cases entfernt
      (IRON_STORAGE / COAL_STORAGE / IRON_BAR_STORAGE / WATER_TANK / FOOD_SILO /
      OXYGEN_STORAGE) → **1 generic WAREHOUSE** (500 m³/Lvl)
- [x] Base-Volume `Planet::BASE_VOLUME_CAPACITY = 5000` m³ (pragmatisch
      erhöht vom Ticket-Vorschlag 50, damit Onboarding ohne Wand startet)
- [x] HQ trägt 25 m³/Lvl bei (Q2 Decision); andere Buildings 50 m³/Lvl
      (Mines/Refineries), 100 m³/Lvl (Recycling-Plant), 0 (HUB/QoL/Strategic)
- [x] Production-Processors (Mining, Refinement, Renewable) clampen weiterhin
      gegen `getStorageCapacity(R)` (Legacy-Shim) → Volume-Cap-Stop (Q3=a)
- [x] Demo / Tests: 648/648 grün
- [x] Doc `buildings.md` Storage-Sektion refactored
- [x] Doc `resources.md` Volume-Sektion aktualisiert
- [x] Doc `decisions.md` Eintrag: "Storage = Generic Volume-Based, T-061 superseded"

## Out of Scope

- Ship-Cargo-Refactor (T-178)
- Pop-als-Storage-Item (T-179)
- Size-Multiplier-Tabelle (T-180 — Foundation)
- Trade-Routes / Auto-Export (T-110)

## Notes

- T-061 wird damit **Superseded** (analog T-024 → T-103 Pattern)
- Reset-fähigkeit der Demo-DB hilft — kein hartes Daten-Migration nötig
- Refactor-Reihenfolge: T-180 (Config) → T-177 (Planet) → T-178 (Ship) → T-179 (Pop)
