# T-173: HQ-Level als Building-Cap + steile HQ-Kosten + Speed-Bonus

**Type:** Feature
**Epic:** Building System
**Domain:** Building
**Blocked By:** T-172
**Status:** Ready
**Effort:** M (~3h)
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

### Q1: Cap-Rule Scope — RESOLVED = (a)

**Decision:** HQ-Cap gilt für **ALLE** Buildings inkl. Unique-Strategics
(Shipyard, Research-Lab, Construction-Yard, Probe-Lab, HUB, QoL, Mines,
Storage, Producer, Smelter). HQ selbst ausgenommen. Keine Building auf dem
Planeten darf höher als HQ leveln. HQ ist DAS Pacing-Tor.

### Q2: Cap-Vergleich — RESOLVED = (a)

**Decision:** **Strikt-gleich erlaubt** (`buildingLevel ≤ hqLevel`).
HQ L4 → alle anderen Buildings max L4. Player kann parallel auf gleichem
Level halten. Intuitiver als "HQ eins voraus".

### Q3: HQ-Cost-Curve — RESOLVED = (c) Multi-Resource-Last

**Decision:** Quadratische Multi-Resource-Last. HQ-Upgrades fordern mit
steigendem Level zusätzliche REFINED-Resources in wachsender Menge. Verbindet
HQ-Push mit Industrie-Aufbau (Iron-Smelter → Steel-Smelter → Hull-Foundry-
Chain). Standard `2^level`-Skalierung der Base-Kosten bleibt; hinzu kommen
Tier-2/3-Anteile.

**Vorschlag-Tabelle (final beim Implementation-Start; Tuning-Knob):**

| HQ-Level | Base-Multi | Zusatz-REFINED |
|----------|-----------|----------------|
| 1 (Auto) | ×1 | — (Foundation via ClaimStartPlanet) |
| 2 | ×2 | + 50 IRON_BAR |
| 3 | ×4 | + 100 IRON_BAR + 30 STEEL |
| 4 | ×8 | + 200 IRON_BAR + 100 STEEL + 30 COMPOSITE |
| 5 | ×16 | + 400 IRON_BAR + 250 STEEL + 100 COMPOSITE + 30 HULL_PLATE |
| 6+ | ×2^L | quadratisch wachsende REFINED-Tier-2/3 (Tuning-Folge) |

Konsistent mit T-067 Tier-2-Tree (STEEL/COMPOSITE/HULL_PLATE alle Done).

### Q4: Speed-Bonus — RESOLVED

**Magnitude:** `-3%/Level` Bauzeit-Reduktion (multiplier = `0.97^hqLevel`).
HQ L1 = 0.97×, L5 = 0.86×, L10 = 0.74× (~74% Bauzeit).

**Stack:** **Multiplikativ** mit anderen Speed-Sources:
```
effective_speed_multi = HQ_speed × T-064_research × T-064b_yard × T-063_type
```
Multiplikativ schaltet die volle Stack-Power frei (z.B. HQ L10 × T-064 L3
× CONSTRUCTION_YARD L5 × BARREN-Planet ≈ Bauzeit-Halbierung).

### Q5: Max-HQ-Level — RESOLVED = (c) Unbegrenzt

**Decision:** Keine harte `maxLevel`-Konstante für HQ. Die quadratisch
wachsenden Resource-Kosten (Q3) + REFINED-Tier-Anforderungen regeln Late-Game
natürlich. Cost wird zum Bottleneck statt Level-Cap. Konsistent mit
"HQ ist Pacing-Tor".

T-094c Slot-Cap-Skalierung (HQ L5+ liefert +1 Build-Queue-Slot) funktioniert
weiterhin — kein Cap-Konflikt.

### Q6: Retroactive-Behavior — RESOLVED = (c)

**Decision:** Situation kann garnicht entstehen — T-172 baut HQ L1 auto-
matisch beim `ClaimStartPlanet`, Cap gilt von Anfang an. Demo-Reset cleart
alles. Migration nicht erforderlich.

Falls in Production-DB doch Demo-Buff-Player mit existing Buildings > HQ
existieren: Fallback = Grandfather (Buildings bleiben, kein Upgrade bis
HQ aufholt). Aber das ist Edge-Case ohne Test-Coverage in T-173.

### Q7: Cap-Check Initial-Build — RESOLVED = (a)

**Decision:** Cap-Check läuft auf **Initial-Build + Upgrade**. Initial-Build
ist trivial-OK (L1 ≤ HQ-Level wenn HQ ≥ L1 via T-172 Auto-Bootstrap). Real-
wirksam ist der Cap-Check auf `UpgradeBuildingCommand`.

### Q8: Error-Verhalten — RESOLVED

**Decision:** Domain-Exception + Demo-CLI Hint.

- `HqLevelCapViolationException(BuildingType $type, int $target, int $hqLevel)`
  mit Message: "Cannot upgrade <type> to L<target> — HQ is L<hqLevel>"
- Demo-CLI Upgrade-Action: `[BLOCKED: HQ is L<hqLevel>]` neben gecappten
  Buildings im Preview-Menu (Cap-Status sichtbar BEVOR Player es versucht)
- Cost-Preview im Build/Upgrade-Menü zeigt zusätzlich
  `HQ-Cap: Buildings ≤ L<hqLevel>` als globalen Hinweis

## Acceptance Criteria

### Cap-Mechanik (Q1/Q2/Q7)

- [ ] `Planet::canUpgradeBuilding(Building $b): bool` — prüft ob
      `b.level + 1 ≤ hqLevel`. HQ selbst ausgenommen.
- [ ] `UpgradeBuildingCommand` wirft `HqLevelCapViolationException(type, target, hqLevel)`
      bei Verletzung — VOR Resource/Pop-Validation
- [ ] Initial-Build wird ebenfalls geprüft (trivial-OK wenn HQ ≥ L1 via T-172)
- [ ] Cap betrifft ALLE non-HQ Buildings (inkl. Strategic + QoL + Mines etc.)

### HQ-Cost-Curve (Q3)

- [ ] `BuildingCostConfig` für HQ liefert Multi-Resource-Cost je Level:
      - L2: Base + 50 IRON_BAR
      - L3: Base + 100 IRON_BAR + 30 STEEL
      - L4: Base + 200 IRON_BAR + 100 STEEL + 30 COMPOSITE
      - L5: Base + 400 IRON_BAR + 250 STEEL + 100 COMPOSITE + 30 HULL_PLATE
      - L6+: quadratisch wachsend, Per-Level-Tuning offen
- [ ] HQ-Duration ebenfalls steiler — Vorschlag: bestehende `2^level`-Skalierung
      verdoppelt für HQ (Effective `4^level`)
- [ ] Kein `maxLevel`-Cap auf HQ (Q5) — Cost regelt natürlich

### Speed-Bonus (Q4)

- [ ] `Planet::getHqBuildSpeedMultiplier($now): float` = `0.97^hqLevel`
      (HQ L1 = 0.97, L10 = ~0.74)
- [ ] `BuildBuildingCommandService` + `UpgradeBuildingCommandService`
      multiplizieren HQ-Speed in den existing Speed-Stack:
      `effective = HQ × T-064_research × T-064b_yard × T-063_type`

### Error-Verhalten + UI (Q8)

- [ ] `HqLevelCapViolationException` mit klarer Message
- [ ] Demo-CLI Upgrade-Action zeigt `[BLOCKED: HQ is L<n>]` neben gecappten
      Buildings im Preview-Menu
- [ ] Demo-CLI Cost-Preview zeigt `HQ-Cap: Buildings ≤ L<n>` als Global-Hint

### Tests

- [ ] `HqLevelCapTest` (IT): Upgrade-Block bei building.level == hqLevel
- [ ] `HqLevelCapTest`: HQ-Upgrade öffnet Upgrade-Pfad wieder
- [ ] `HqLevelCapTest`: HQ selbst nicht gecapped (HQ-Upgrade möglich)
- [ ] `HqCostCurveTest`: Multi-Resource-Cost Werte je Level
- [ ] `HqBuildSpeedTest`: 0.97^level Multiplier, Stack mit T-064/T-064b/T-063
- [ ] `BuildBuildingCommandTest`: Initial-Build trivial-OK trotz Cap

### Docs

- [ ] `buildings.md` HQ-Cap-Section + Cost-Curve-Tabelle + Speed-Bonus
- [ ] `decisions.md` Eintrag für T-173 HQ-Cap-Mechanik

## Fixtures Needed

No. Tests nutzen direkt `Planet::generatePlanet()` + Building-Constructor.

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

### Refinement Tokens (estimate)
- Input: ~10k
- Output: ~3k
