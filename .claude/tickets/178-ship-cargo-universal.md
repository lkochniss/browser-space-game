# T-178: Ship-Cargo-Universal (alle Schiffe haben Cargo)

**Type:** Feature (Refactor)
**Status:** Blocked (by T-177)
**Effort:** M (~4-5h)
**Depends on:** T-177 (Generic-Storage-Refactor), T-180 (Size-Multiplier-Config)
**Blocks:** T-066 (Fuel-Storage auf Ship), T-105 (Schiff-Maintenance — Fuel-Consumption)

## Beschreibung

Storage-Vision-Pivot (T-177) erweitert sich auf Schiffe: **Jedes Schiff
hat eigenen Cargo-Volumen-Storage**, auch Non-Transporter-Klassen — für
eigene Versorgung (Fuel, Pop-Survival, Notreserven). Transport-Klassen
haben weiterhin den größten Cargo, aber sind nicht mehr exklusiv.

Aktuell (T-015 Done): nur `TransportShipClass` hat `CargoManifest`. Andere
Ships haben keinen Cargo-Slot. Neuer Stand: alle Ships haben Cargo,
volumengebunden.

## Open Questions

### Q1: Cargo-Größe pro Ship-Klasse

Wie groß ist der Cargo-Volumen pro Klasse?

- (a) **Per ShipType konfiguriert** — jede ShipType-Enum-Variante hat eigene `cargoVolume`-Konstante (z.B. CARGO_LIGHT=500, CARGO_MEDIUM=2000, CARGO_HEAVY=10000, SCOUT=50, FRIGATE=200, BATTLESHIP=400, COLONY_SHIP=300, PROBE=10)
- (b) **Linear nach Schiff-Mass-Class** — drei Mass-Tiers (S/M/L), Cargo skaliert linear (z.B. S=100, M=500, L=2000)
- (c) **Hybrid** — Base-Cargo (10-50, für Eigen-Versorgung) + Mass-Tier-Bonus + Spezialisierung (Transport-Klassen ×5)

### Q2: Was bedeutet "Eigen-Versorgung"?

Für Non-Transporter (z.B. Frigatte) — was muss in Cargo passen?

- (a) **Nur Fuel** (T-066) — Schiff trägt Treibstoff für Reichweite
- (b) **Fuel + Pop-Survival-Ration** (T-105 Folge) — Crew braucht W/F/O für Reise
- (c) **Fuel + Pop + Loot** — Battleship kann auch Loot mitnehmen (Salvage, Kriegsbeute)

### Q3: Migration T-015 CargoManifest

T-015 (Done) hat `CargoManifest` Embeddable für Transport-Klassen. Was passiert?

- (a) **CargoManifest komplett ersetzen** durch generic `ShipCargo` (analog T-177 Storage)
- (b) **CargoManifest umbenennen** zu `ShipCargo`, beibehalten, Volume-Logic einbauen
- (c) **CargoManifest bleibt für Transport-Klassen** (legacy), neue `ShipCargo` nur für Non-Transporter (gemischt — komplex)

### Q4: Load/Unload-API

T-015 hat `LoadCargoCommand`/`UnloadCargoCommand` für Transport-Klassen.

- (a) **Generisch für alle Ships** — Commands akzeptieren beliebige Ship-IDs; Volume-Check intern
- (b) **Eigene Commands für Non-Transporter** — z.B. `RefuelShipCommand` für Fuel-Only-Subset
- (c) **Beibehalten + erweitern** — Load/Unload-Commands für alle, plus convenience-Commands für häufige Cases (Refuel, Pop-Embark)

### Q5: Default-Cargo-Content bei Bau

Wenn ein Schiff gebaut wird (BuildShipCommand): bekommt es Initial-Cargo?

- (a) **Leer** — Player muss explizit Cargo laden vor Reise
- (b) **Auto-Refuel** — Ship startet mit voll Fuel für seine Reichweite (analog "tank voll bei Auslieferung")
- (c) **Auto-Refuel + Pop-Crew-Ration** — Standard-Reise-Ausstattung

## Acceptance Criteria (Draft — final nach Q1-Q5)

- [ ] `Ship::cargoVolumeCapacity: int` (statisch pro ShipType)
- [ ] `Ship::cargo: ShipCargo` (Embeddable, generic Item-Storage analog T-177)
- [ ] Volume-Check bei Load/Unload (Q4)
- [ ] Cargo-Größen-Tabelle pro ShipType (Q1)
- [ ] T-015 CargoManifest-Refactor (Q3)
- [ ] Load/Unload-API generalisiert (Q4)
- [ ] Initial-Cargo-Setup beim Build (Q5)
- [ ] Tests: Cargo-Volume-Check, Multi-Item-Cargo, Fuel-Verbrauch (Stub für T-105)
- [ ] Doc `ships.md` Cargo-Sektion komplett überarbeitet

## Out of Scope

- Fuel-Verbrauch-Logic im Flug (T-105)
- Pop-Mortality bei Crew-Mangel (T-105 Folge)
- Trade-Routes (T-110) Auto-Cargo-Management

## Notes

- T-015 wird **erweitert**, nicht superseded (CargoManifest war nur für
  Transport-Klassen — nun für alle)
- T-015c (Pop-Transfer Ship↔Station, Draft) ist davon betroffen → muss
  Volume-Logic mitkriegen wenn T-179 done
