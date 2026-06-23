# T-173: HQ-Level als Building-Cap + steile HQ-Kosten + Speed-Bonus

**Type:** Feature
**Epic:** Building System
**Domain:** Building
**Blocked By:** T-172
**Status:** Draft (Decisions pending — siehe Open Questions)
**Effort:** M (~2-3h, abhängig von Q1/Q6)
**Depends on:** T-172 (HQ-Foundation)
**Related:** T-064 (construction_speed Research), T-064b (CONSTRUCTION_HUB Speed-Multi), T-094 (Build-Queue)
**Blocks:** —

## Beschreibung

Drei verbundene HQ-Mechaniken, die zusammen ein konsistentes Late-Game-Pacing
für Planet-Ausbau erzeugen:

1. **Building-Level-Cap:** Kein Building auf einem Planeten darf höher leveln
   als das HQ desselben Planeten. HQ wird zum strategischen Bottleneck — Player
   muss HQ zuerst pushen, bevor andere Strukturen mitziehen können.
2. **Steile HQ-Kostenkurve:** Späte HQ-Level dauern extrem lange und kosten
   überproportional viel (mehr als Standard `2^level`). Das verhindert
   trivialen Late-Game-Spam und macht HQ-Pushen zu einer echten Investition.
3. **Speed-Bonus:** HQ gibt kleinen lokalen Build-Speed-Bonus pro Level — als
   Kompensation für teure Upgrades und Anreiz, doch zu pushen.

Effekt: HQ ist das Rückgrat des Planeten-Ausbaus. Tempo des gesamten Planeten
ist an HQ-Tempo gekoppelt.

## Open Questions (vor Implementation klären)

### Q1: Cap-Rule Scope

Welche Buildings unterliegen dem HQ-Cap?

- (a) **Alle** — inklusive andere Unique-Strategic (Shipyard, Research-Lab,
  Construction-Hub, Probe-Lab, ggf. HUB-Familie)
- (b) **Nur "normale" Buildings** — Production/Storage/Renewable; andere
  Unique-Strategic bleiben unabhängig (eigener Tech-Gate-Pfad)
- (c) **HQ selbst ausgenommen** (klar) + alle restlichen Buildings kapped

### Q2: Cap-Vergleich (≤ vs <)

- (a) **Strikt-gleich erlaubt** (HQ L4 → Buildings max L4)
- (b) **Strikt-kleiner** (HQ L4 → Buildings max L3) — HQ immer "eins voraus"

### Q3: HQ-Cost-Curve

Welche Form soll die Steilheit annehmen?

- (a) **Höhere Exponential-Base** (z.B. `3^level` statt `2^level`)
- (b) **Step-Function** — moderate Cost bis L3, danach harte Sprünge (z.B.
  L4: ×4, L5: ×8, L6: ×16 statt linear ×2)
- (c) **Quadratische Multi-Resource-Last** — höhere Tiers brauchen REFINED
  Resources (Iron-Bar, Steel) in steigender Menge
- (d) **Konkrete Tabelle vorgegeben** — Player gibt Cost/Duration je Level
- **Default-Vorschlag:** `2^level` für L1-L3, dann `(level^3)` Multiplier ab L4

### Q4: Speed-Bonus Magnitude + Stack

- Magnitude: **-2%/Level**, **-5%/Level**, **-10%/Level**, oder anders?
- Stack-Verhalten:
  - (a) Multiplikativ mit T-064 (construction_speed-Tech) + T-064b
    (CONSTRUCTION_HUB) + T-063 (Planet-Type)
  - (b) Additiv mit gleichen Sources
- **Default-Vorschlag:** -3%/Level, multiplikativ — bei L10 = ~74% Bauzeit
  (`0.97^10`)

### Q5: Max-HQ-Level

- (a) **L10** — moderate Endgame-Grenze
- (b) **L15** — viel Raum für Late-Game-Progress
- (c) **Unbegrenzt** — exponentielle Cost regelt von selbst
- **Default-Vorschlag:** L10 (analog T-094c Slot-Cap-Skalierungs-Ideen)

### Q6: Retroactive-Behavior (Existing-Buildings)

Was passiert, wenn bei Einführung bereits Buildings höher als HQ stehen
(z.B. HQ L2, aber Iron-Mine L4)?

- (a) **Grandfather** — bestehende bleiben, aber kein weiteres Upgrade bis
  HQ aufgeholt hat
- (b) **Hard-Block** — Migration setzt HQ auto auf Max(Existing-Building-Level)
- (c) **Nicht relevant** — wenn T-172 vorgibt "HQ L1 ab Planet-Claim" und
  Cap-Rule von Anfang an gilt, kann diese Situation nicht entstehen (außer
  bei Migration alter Demo-State)

### Q7: Cap auch für Initial-Build, oder nur Upgrade?

- (a) **Initial + Upgrade** — neue Buildings werden direkt mit L1 gebaut;
  Cap betrifft nur Upgrade-Pfad (trivial, da L1 ≤ alles)
- (b) **Strikt** — wenn Building-Initial L1 ist und HQ noch L0 (theoretisch),
  Block Initial-Build bis HQ existiert
- **Default-Vorschlag:** (a) — Initial-Build = L1, automatisch ≤ HQ wenn
  HQ ≥ L1 (Voraussetzung via T-172 Auto-Bootstrap)

### Q8: Error-Verhalten beim Cap-Violation

- Domain-Exception bei `UpgradeBuildingCommand` mit klarer Message
  ("Cannot upgrade BUILDING to L5 — HQ is L4")?
- UI-Hint im Demo-CLI: "Upgrade HQ first" wenn Cap-Block?

## Acceptance Criteria (Draft — final nach Q1-Q8)

- [ ] HQ-Cost-Konfiguration mit steiler Late-Game-Curve (Q3)
- [ ] HQ-Duration-Skalierung analog steiler (Q3)
- [ ] HQ `maxLevel` Cap-Konstante (Q5)
- [ ] `Planet::canUpgradeBuilding(BuildingType, level)` prüft HQ-Cap (Q1, Q2, Q7)
- [ ] `UpgradeBuildingCommand` wirft Domain-Exception bei Cap-Violation (Q8)
- [ ] `BuildingType::HQ` liefert `getBuildSpeedMultiplier(level)` für Speed-Bonus (Q4)
- [ ] Speed-Bonus stackt korrekt mit T-064/T-064b/T-063 (Q4)
- [ ] Integration-Tests:
  - Upgrade-Block wenn Building bereits = HQ-Level (Q2 hängt von a/b ab)
  - HQ-Upgrade öffnet wieder Building-Upgrade-Path
  - Cost-Curve-Werte je Level
  - Speed-Bonus wirkt auf neue Build-Aufträge
  - Stack-Berechnung mit anderen Speed-Sources
- [ ] Demo-CLI: Cost-Preview zeigt HQ-Cap-Status ("HQ L3 → Buildings ≤ L3")
- [ ] Doc: `buildings.md` updated (HQ-Cap-Section, Cost-Curve, Speed-Bonus)
- [ ] Doc: `decisions.md` Eintrag für Cap-Mechanik

## Out of Scope

- HQ-Cap-Override via Forschung (späteres Logistics-Tree-Feature)
- Multi-Planet-HQ-Synergien (Reichs-HQ-Konzept)
- HQ-Defense-Boni / HQ als Combat-Target
- Cap-Bypass für temporäre Events (Crusade/Buffs)

## Notes

- Setzt T-172 voraus: HQ als eigener BuildingType existiert, Auto-Build bei
  ClaimStartPlanet ist geklärt (Q4 in T-172).
- Bei Q3-Cost-Curve: Wechselwirkung mit T-094c (Build-Queue-Slots via Hub-
  Upgrade) beachten — wenn HQ teuer wird, sollte Player auch Slots dafür
  haben, um andere Buildings parallel zu pushen während HQ wartet.
- Speed-Bonus + Cap = klassisches Pacing-Pattern: "HQ-Investition zahlt sich
  in Tempo aus, aber das Tempo gilt nur, wenn man die Investition macht."
