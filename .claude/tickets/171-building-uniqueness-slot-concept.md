# T-171: Building-Uniqueness + Planet-Slot-Konzept

**Type:** Feature
**Epic:** Building System
**Domain:** Building
**Blocked By:** T-009, T-094, T-170
**Status:** Done (Foundation implementiert; Folge: Cancel/Demolish/Slot-Bonus)
**Effort:** M (~3h, abhängig von Decisions)
**Depends on:** T-009 (Building-Cost), T-094 (Bau-Queue Foundation), T-170 (Tech-Tree)
**Blocks:** Tier-2/3-Building-Erweiterungen (T-067, T-097, T-100, T-107) profitieren von einem Slot-Konzept

## Beschreibung

Heute kann auf einem Planeten dasselbe Building beliebig oft gebaut werden
(z.B. 5× IRON_MINE). Funktional läuft das, aber semantisch ist das oft
unsinnig (5× Hub macht Pop-Cap absurd hoch; 3× Research-Lab ist absurd).

User-Wunsch: pro Planet **nur 1 Instanz** pro Building-Type, dann nur Upgrade
(Level steigt) statt zweite Instanz. Aber: für gewisse Buildings (Mines,
Storage) wäre Mehrfach-Bau auf höheren Levels eventuell sinnvoll.

Außerdem: ohne Slot-Limit kann der Planet als Bau-Liste unbegrenzt wachsen.
Real-Game-Design braucht **Maximal-Bauplätze pro Planet**.

## Design-Dimensionen

### A. Uniqueness pro Building-Type

3 Kategorien denkbar:

| Kategorie | Building-Liste (Vorschlag) | Verhalten |
|-----------|----------------------------|-----------|
| **Strikt unique** | HUB, RESEARCH_LAB, SHIPYARD, PROBE_LAB, RECYCLING_PLANT, TELESCOPE | Max 1 pro Planet, Folge-Build = Upgrade-Hint |
| **Multi-Instance erlaubt** | Mines (alle 7), Storage-Buildings (alle 6), W/F/O-Producer (T-097a, alle 3), Smelter | Mehrere Instanzen mit eigenen Levels |
| **Limit-N** (optional) | — | Nicht in Foundation; eventuell für Mines (max 3 pro Type?) |

### B. Planet-Slot-Cap

Max wie viele Buildings (= Bau-Slots) gibt's pro Planet? Heute unbegrenzt.

Vorschlag: cap dynamisch nach `PlanetSize`:

| Size | Slots-Vorschlag |
|------|-----------------|
| TINY | 6 |
| SMALL | 9 |
| MEDIUM | 12 |
| LARGE | 18 |
| HUGE | 25 |

Strikt-unique Buildings (z.B. HUB) zählen wie reguläre Slots.

### C. Migration / Existing-Gameplay

- Aktueller Demo-Player hat ggf. schon mehrere Builds desselben Typs (theoretisch)
- Eintragsstrategie: bei Implementierung Validation via "first-of-type already exists" → reject + suggest Upgrade

## Open Questions (vor Implementation klären)

1. **Strikt-unique-Liste:** Welche Buildings sollen wirklich strikt unique sein?
   - Klar: HUB, RESEARCH_LAB (User-Vorschlag)
   - Wahrscheinlich: SHIPYARD, PROBE_LAB, RECYCLING_PLANT, TELESCOPE (Strategic Buildings)
   - Diskussion: IRON_SMELTER (alleinig Refinement-Building heute)
2. **Mines: Multi oder Single?**
   - Single = klar, Upgrade-only
   - Multi = Player kann mehrere mit unterschiedlichen Levels parallel betreiben — Diversification-Strategie
   - Real-World-Genre-Vergleich: Stellaris=Multi mit Slot-Limit, OGame=Single-Upgrade-only
3. **Planet-Slot-Cap:** Werte wie oben? Oder anderes Schema (z.B. Hub-Level = +N Slots)?
4. **Storage-Buildings:** Multi sinnvoll (Cap-Stacking) oder Single+Level (Cap pro Level)?
5. **Migration für Demo-Welt:** Drop-and-recreate ist OK (Demo wird sowieso resetbar), aber Tests brauchen Anpassung
6. **Folge-Tickets:** Bau-Queue (T-094) Slots-pro-Planet mit Bauten-Slots verzahnen? Aktuell sind das 2 verschiedene Konzepte.

## Acceptance Criteria (Draft, abhängig von Decisions)

- [ ] `BuildingType` neu: Method `isUnique(): bool` (oder dezidierte Lookup-Tabelle)
- [ ] `BuildBuildingCommandService` validiert: wenn `isUnique` und Planet hat schon Instanz → `BuildingAlreadyExistsException` mit Upgrade-Hint
- [ ] `Planet::getBuildingSlotsCap()` neu, `Planet::getBuildingSlotsUsed()` neu
- [ ] `BuildBuildingCommandService` validiert Slot-Cap → `PlanetSlotsFullException`
- [ ] Demo-CLI Status zeigt "Slots: N/M" pro Planet
- [ ] Demo-CLI Build-Menu zeigt unique-Buildings mit "(unique, upgrade-only)" wenn schon vorhanden
- [ ] Tests: 5+ Unit (unique-reject, slot-cap-reject, upgrade-still-works, mines-multi-allowed, demo-shows)
- [ ] Doc: buildings.md (Unique-Tabelle + Slot-Mechanik)
- [ ] Suite grün

## Out of Scope

- **Demolish/Refund** (separater Folge-Ticket bei Bedarf)
- **Slot-Bonus über Forschung** (Logistics-Branch — siehe T-094c)
- **Specialist-Buildings ersetzen Slots** (z.B. SPECIAL nimmt 2 Slots) — späteres Tuning

## Notes

- Foundation-Decision: User schlug "Hub + Research_Lab unique" → entwickle daraus
  konsistente Strikt-Unique-Liste
- Bau-Queue (T-094) limitiert PARALLELE Bauten (3 simultan); Slot-Cap limitiert
  TOTAL-Bauten (z.B. 12 auf Medium-Planet) → komplementär, kein Konflikt
- Cleanup-Ticket T-167 listed bereits einige related Loose Ends — eventuell
  T-171-Decisions dort mit absorbieren
