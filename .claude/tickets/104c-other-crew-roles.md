# T-104c Andere Crew-Rollen (Engineer + Diplomat)

**Type:** Feature
**Epic:** Combat & Battle
**Domain:** Ship
**Blocked By:** T-104a, T-110
**Status:** Ready
**Effort:** M (~4h)
**Depends on:** T-104a (Ready), T-110 (Ready), T-073 (Done — Faction-Foundation), T-105 (Blocked — Maintenance-Hook für Engineer)
**Blocks:** —

## Beschreibung

Erweitert Crew-System (T-104a) um Non-Combat-Rollen.

**Rollen (Scope-Cut):**
- **Engineer**: assigned an Schiff → reduziert Maintenance-Cost (T-105 Hook)
- **Diplomat**: assigned an Faction → Tier-Stufen Boni (Rep + Mission + Trade)
- ~~Forscher~~: gestrichen (Multi-Lab T-025c deckt Research-Speed bereits ab)
- **Spy**: bleibt in T-131 (komplexer Mission-Mechanism)

## Resolved Decisions

- **Q1 Forscher gestrichen:** Multi-Lab-Opt-In (T-025c Done) ist die kanonische
  Research-Speed-Lösung. Forscher als Schiffs-Crew überlappt zu sehr —
  Komplexität ohne Mehrwert.
- **Q2 Engineer-Effekt:** Maintenance-Cost ×(1 - 0.05 × engineer.level), max -50%
  bei L10. Wirkt auf T-105 Treibstoff/Crew-Versorgung wenn dort done.
  Bis T-105 Done ist Engineer Stub-Effekt (kein Maintenance vorhanden zum reduzieren).
- **Q3 Diplomat-Effekt (Tier-Stufen, L1-10 cumulative):**
  - **L1-3:** Reputation-Speed ×(1 + 0.05 × min(3, level)) — bis +15%
  - **L4-6:** Faction-Mission-Success-Chance +(level - 3) × 5% (L4=+5%, L6=+15%) on top
  - **L7-10:** Trade-Hub-Discount der assigned Faction
    `cost × (1 - 0.02 × (level - 6))` — L7=-2%, L10=-8%
  - Effekte sind cumulative: L10-Diplomat hat alle 3 Boni
- **Q4 Training-Rate (Pro-Rolle):**
  - Captain: `60min × 2^captainCount` (T-104a)
  - Engineer: `45min × 1.8^engineerCount`
  - Diplomat: `90min × 2^diplomatCount` (rare Profession)

## Acceptance Criteria

### Crew-Type-Enum Erweiterung

- [ ] `CrewType` erweitert: `CAPTAIN` (T-104a) + `ENGINEER` + `DIPLOMAT`
- [ ] `CrewType::getTrainingDurationFormula(): callable` (Lookup per Type
      gegen Decision Q4)

### Akademie-Training-Erweiterung

- [ ] `StartCrewTrainingCommand(playerId, academyPlanetId, crewType)` —
      generisch, nicht Captain-spezifisch
- [ ] Officer-Quarters-Cap (T-104a) deckt ALLE Crew-Types (kein per-Type-Cap)
- [ ] CrewType-spezifische Duration-Formel angewandt

### Engineer-Effekt (T-105 Hook, Stub-Behavior bis T-105)

- [ ] `Ship::getMaintenanceMultiplier(): float`
      `= max(0.5, 1 - 0.05 × max(0, engineer.level)) if engineer assigned else 1.0`
- [ ] T-105 nutzt das beim Maintenance-Tick (sobald T-105 done)
- [ ] Bis dahin: Hook-Stub mit Test, dass Multi korrekt berechnet wird

### Diplomat-Effekt (T-073 ReputationService Hook)

- [ ] `Faction` hat optional `assignedDiplomat: ?Crew`
- [ ] `AssignDiplomatCommand(diplomatId, factionSlug)`
- [ ] `ReputationService::changeReputation()` multipliziert Rep-Delta
      mit `1 + 0.05 × min(3, diplomat.level)` falls Diplomat assigned
- [ ] Mission-Success-Chance-Hook (T-078 Faction-Quest-Storylines wenn done):
      `+5% × (level - 3) clamp(0, ...)` ab L4
- [ ] Trade-Hub-Discount-Hook (T-112 wenn done):
      `cost × (1 - 0.02 × (level - 6) clamp(0, 4))` ab L7

### Demo CLI

- [ ] "Start Crew Training" Action erweitert um Type-Selection (Captain/Engineer/Diplomat)
- [ ] "Assign Diplomat to Faction" Action
- [ ] Status-Display: pro Player Crew-List inkl. Type + assigned-Entity

### Tests

- [ ] `EngineerMaintenanceMultiplierTest`: Multi-Formel pro Level
- [ ] `DiplomatReputationBoostTest`: Rep-Speed-Multi
- [ ] `DiplomatMissionSuccessTest`: Success-Chance ab L4
- [ ] `DiplomatTradeDiscountTest`: Trade-Hub-Discount ab L7
- [ ] `CrewTrainingDurationTest`: Pro-Type-Formel correct
- [ ] `OfficerQuartersCapAllTypesTest`: Cap deckt alle Crew-Types

### Docs

- [ ] `crew.md` (T-104a doc) erweitert um Engineer + Diplomat
- [ ] `decisions.md` Eintrag T-104c (inkl. "Forscher gestrichen, T-025c deckt's ab")

## Out of Scope

- Spy-Crew → T-131
- Forscher-Crew → permanent dropped (T-025c reicht)
- T-105 Maintenance-Implementation (T-104c liefert nur Engineer-Multi-API)
- T-078 Faction-Quest (T-104c liefert nur Diplomat-Success-Chance-Hook)
- T-112 Trade-Hub (T-104c liefert nur Diplomat-Discount-Hook)

## Fixtures Needed

Yes — `EngineerFixture` + `DiplomatFixture` mit Crew-Pools in verschiedenen
Levels + Assignments.

## Notes

- Forscher-Cut: T-025c Multi-Lab Opt-In ist die etablierte Lab-Speed-Lösung.
  Forscher-Crew wäre redundant.
- Diplomat-Tier-Stufen sind cumulative: L10-Diplomat hat alle 3 Boni
  (Rep + Mission + Trade). Macht L10-Diplomat zur tier-1-Investment.
- Engineer-Multi ist Stub-Effekt solange T-105 Maintenance noch nicht implementiert
- Crew-Pool-Total-Cap (Officer-Quarters) ist shared zwischen Captain/Engineer/Diplomat
  → Player muss priorisieren

### Refinement Tokens (estimate)
- Input: ~8k
- Output: ~3k
