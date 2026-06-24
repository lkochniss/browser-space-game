# T-104a Crew-Foundation (Akademie + Captains)

**Type:** Feature
**Epic:** Combat & Battle
**Domain:** Ship
**Blocked By:** T-009, T-070
**Status:** Done
**Effort:** L
**Depends on:** T-009 (Building-Cost), T-070 (Pop-QoL via Officer-Quarters)
**Blocks:** T-102, T-104b, T-104c

## Beschreibung
Captains als limited Resource. Pro Combat-Schiff genau 1 Captain nötig. Captains werden in Akademie ausgebildet — eigene Bauzeit, eigene Pop-Bindung.

Foundation-Scope: Akademie-Building + Crew-Entity + Captain-Type. Skill-Trees (T-104b) + andere Rollen (T-104c) als Folgetickets.

## Resolved Decisions

- **Q1 Training-Rate (c):** Wallclock-Formel `duration = 60min × 2^captainCount`
  pro Player. Erste Captains schnell, später zunehmend teuer. Skaliert
  natürlich mit Player-Progress.
- **Q2 Captain-Stats (b):** `+3%/Lvl` auf Damage, HP, Schild. Captain L10 = +30%
  pro Stat. Moderater Boost ohne Game-Breaker.
- **Q3 Level-Up (c):** Doppel-Pfad — Combat-XP automatisch via Battle-Survival
  (T-103 Hook) + optionale Akademie-Boost-Action (Resource-Cost, kein Combat
  erforderlich). Threshold-basiert.
- **Q4 Permadeath (c) mit Escape-Pod-Roll:** Captain stirbt mit Schiff,
  AUSSER Schiff hat Escape-Pod (T-102 ShipType-Stat). Mit Pod: Captain hat
  X% Survival-Chance (Pod-Quality-Stat pro ShipType, z.B. Escort 50%,
  Battleship 80%). Auf Survive: Captain wird IDLE auf nächst-gelegenem
  Heimat-Planet.

## Acceptance Criteria

### Crew-Domain Foundation

- [x] `App\Crew\` Domain-Folder (neu): Model/Crew, ValueObject/CrewId+CrewType+CrewStatus
- [x] `CrewType` Enum (Foundation: nur `CAPTAIN`; T-104c erweitert)
- [x] `CrewStatus` Enum: `TRAINING`, `IDLE`, `ASSIGNED`, `DEAD`
- [x] `Crew` Entity: id, type, level (1-10), owner (Player), status,
      assignedShip (nullable), xp, trainingFinishedAt (nullable)
- [x] Domain-Repo + Doctrine ORM-Mapping + Migration

### Buildings

- [x] `BuildingType::ACADEMY` — produziert Crew (Wallclock-Training)
- [x] `BuildingType::OFFICER_QUARTERS` — Crew-Cap-Booster (max 5 Crew/Level)
- [x] BuildingCostConfig + BuildingDurationConfig + getSlotSize=2 für beide
- [ ] BuildingUnlockConfig: ACADEMY hinter Research-Gate (slug TBD) — _deferred:
      kein passender Research-Node existiert, Foundation lässt ACADEMY frei
      baubar; Gate-Hook in T-104b oder eigenem Folge-Ticket_

### Training

- [x] `StartCrewTrainingCommand(playerId, type)`
- [x] Service: validiert Akademie ready, Crew-Cap nicht erreicht
- [x] `Crew::startTraining()` → Status=TRAINING, trainingFinishedAt = now + duration
- [x] Duration = `60 × 60 × 2^captainCount` Sekunden (Wallclock-Formel)
- [x] `CrewTrainingCompletionService` (Tick, global): wenn finishedAt ≤ now
      → Status TRAINING → IDLE, level=1

### Captain-Cap

- [x] `Player::getCrewCap(): int` = Σ(OfficerQuarters.level × 5) über alle Planeten
- [x] Training-Service rejects wenn `currentCrew >= cap`

### Assignment + Stats

- [x] `AssignCrewCommand(crewId, shipId)` / `UnassignCrewCommand`
- [x] `Crew::getStatsMultiplier()` = `1 + 0.03 × level` (Battle-Engine T-103 liest's)
- [x] Validierung: Crew.status=IDLE, Ship hat keinen anderen Captain

### Level-Up (Q3)

- [x] Crew XP-Felder: `xp: int`, XP-Threshold-Table (L2=100, L3=250, L4=500,
      L5=1000, L6=1750, L7=2500, L8=3250, L9=4000, L10=5000)
- [x] Battle-Hook-Stub: `Crew::addXp()` öffentlich, T-103 ruft nach
      Battle-Survival auf (Wiring in T-103 Folge)
- [x] `BoostCrewCommand(crewId)` Action: 500 IRON_BAR + 100 CHIP → +500 XP,
      24h Cooldown pro Crew
- [x] Auto-Level-Up wenn XP-Threshold erreicht

### Permadeath + Escape-Pod (Q4)

- [x] `ShipType::getEscapePodSurvivalChance(): int` (Foundation: 0% für alle
      heute existing types; T-102 ShipClasses füllen pro Klasse)
- [ ] Bei Schiff-Loss (`Ship::kill()` / T-103 Battle): Captain-Roll — _Hook in
      T-103 Battle-Resolver; Foundation liefert nur `markDead()` + Pod-Stub_
- [x] DEAD-Crew zählt in `getCrewCap()` nicht mehr (Repository-Filter
      `countAliveByPlayer`)

### Demo CLI

- [x] Action "Crew: Train Captain"
- [x] Action "Crew: Assign to Ship"
- [x] Action "Crew: Boost"
- [x] Tick-Hook zeigt `Crew-trained: N` nach Tick-Forward

### Tests

- [x] `StartCrewTrainingCommandServiceTest` (5): Wallclock-Formel, Cap-Block, Missing-Academy
- [x] `CrewTrainingCompletionServiceTest` (3): TRAINING → IDLE on tick
- [x] `AssignCrewCommandServiceTest` (4): Stats-Multiplier, Already-Captain-Block
- [x] `CrewLevelUpTest` (7): XP-Threshold, Auto-Level-Up, Boost-Cooldown

### Docs

- [x] `crew.md` (neu) — Domain-Overview
- [x] `decisions.md` Eintrag für T-104a

## Affected Tests

- tests/Crew/Service/CaptainTrainingTest.php
- tests/Crew/Service/CaptainCapTest.php
- tests/Crew/Service/CaptainAssignmentTest.php
- tests/Crew/Service/CaptainLevelUpTest.php
- tests/Crew/Service/CaptainPermadeathTest.php

## Fixtures Needed

Yes — `CrewFixture` mit Test-Akademie + Officer-Quarters + Test-Captain-Pool
in diversen Status (IDLE/ASSIGNED/TRAINING).

## Notes
- Foundation: keine Skill-Trees, nur lineares Level → +stats
- Skill-Trees in T-104b
- Permadeath + Escape-Pod = Spannung mit Hoffnung. Captain-Verlust schmerzt
  aber ist nicht garantiert
- Captain-XP-Hook in T-103 stays Stub bis T-104a Done

### Refinement Tokens (estimate)
- Input: ~12k
- Output: ~4k

### Implementation Tokens (estimate)
- Input: ~280k
- Output: ~40k

### Deferred / Follow-Ups

- `BuildingUnlockConfig` Research-Gate für ACADEMY (kein Slug heute, lose in T-104b)
- Battle-Hook XP-Award + Permadeath-Roll: Wiring in T-103-Chain
- DEAD-Crew Auto-Cleanup nach 7d: nicht Foundation-Need, Folge-Ticket falls relevant
- CrewFixture (Tests bauen Crew aktuell inline, Fixture nicht zwingend)
