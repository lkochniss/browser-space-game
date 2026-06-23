# T-104a Crew-Foundation (Akademie + Captains)

**Type:** Feature
**Epic:** Combat & Battle
**Domain:** Ship
**Blocked By:** T-009, T-070
**Status:** Ready
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

- [ ] `App\Crew\` Domain-Folder (neu): Model/Crew, ValueObject/CrewId+CrewType+CrewStatus
- [ ] `CrewType` Enum (Foundation: nur `CAPTAIN`; T-104c erweitert)
- [ ] `CrewStatus` Enum: `TRAINING`, `IDLE`, `ASSIGNED`, `DEAD`
- [ ] `Crew` Entity: id, type, level (1-10), owner (Player), status,
      assignedShip (nullable), xp, trainingFinishedAt (nullable)
- [ ] Domain-Repo + Doctrine ORM-Mapping + Migration

### Buildings

- [ ] `BuildingType::ACADEMY` — produziert Crew (Wallclock-Training)
- [ ] `BuildingType::OFFICER_QUARTERS` — Crew-Cap-Booster (max 5 Crew/Level)
- [ ] BuildingCostConfig + BuildingDurationConfig + getSlotSize=2 für beide
- [ ] BuildingUnlockConfig: ACADEMY hinter Research-Gate (slug TBD)

### Training

- [ ] `StartCaptainTrainingCommand(playerId, academyPlanetId)`
- [ ] Service: validiert Akademie ready, Crew-Cap nicht erreicht, Pop-Cost free
- [ ] `Crew::generate()` → Status=TRAINING, trainingFinishedAt = now + duration
- [ ] Duration = `60 × 60 × 2^captainCount` Sekunden (Wallclock-Formel)
- [ ] `CrewTrainingCompletionService` (Tick, global): wenn finishedAt ≤ now
      → Status TRAINING → IDLE, level=1

### Captain-Cap

- [ ] `Player::getCrewCap(): int` = Σ(OfficerQuarters.level × 5) über alle Planeten
- [ ] Training-Service rejects wenn `currentCrew >= cap`

### Assignment + Stats

- [ ] `AssignCaptainCommand(captainId, shipId)` / `UnassignCaptainCommand`
- [ ] Ship-Effective-Damage/HP/Schild × `(1 + 0.03 × captain.level)` wenn assigned
- [ ] Validierung: Captain.status=IDLE, Ship hat keinen anderen Captain

### Level-Up (Q3)

- [ ] Captain XP-Felder: `xp: int`, XP-Threshold-Table (level 1→2 = 100 XP,
      2→3 = 250, ... bis L10 = ~5000 XP cumulative)
- [ ] Battle-Hook (T-103 Folge): nach Battle-Survival +XP basierend auf
      Enemy-Power (Stub-Hook in Crew-Service, T-103 ruft auf)
- [ ] `BoostCaptainCommand(captainId)` Action: Player gibt Resources
      (z.B. 500 IRON_BAR + 100 CHIP) → Captain bekommt z.B. 500 XP. Cooldown
      24h pro Captain
- [ ] Auto-Level-Up wenn XP-Threshold erreicht

### Permadeath + Escape-Pod (Q4)

- [ ] `ShipType::getEscapePodSurvivalChance(): int` (Foundation: 0% für alle
      heute existing types; T-102 ShipClasses füllen pro Klasse)
- [ ] Bei Schiff-Loss (`Ship::kill()` / T-103 Battle): Captain-Roll
      `random(100) < pod-chance` → Captain.status=IDLE auf nearestHomePlanet
      sonst `Captain.status=DEAD`
- [ ] DEAD-Captains in `getCrewCap()` nicht mehr gezählt, eventuell auto-cleanup
      nach 7d

### Demo CLI

- [ ] Action "Start Captain Training" (Akademie wählen, Pop-Confirm)
- [ ] Action "Assign Captain to Ship" (Captain + Ship picker)
- [ ] Action "Boost Captain" (Captain + Resource-Confirm)
- [ ] Status-Display: Captain-List mit Level/Status/AssignedShip

### Tests

- [ ] `CaptainTrainingTest`: Wallclock-Formel correct, Cap-Block
- [ ] `CaptainAssignmentTest`: Stats-Bonus auf Ship +3%/Lvl
- [ ] `CaptainLevelUpTest`: XP-Threshold, Auto-Level-Up
- [ ] `CaptainPermadeathTest`: Escape-Pod-Roll, DEAD-Status
- [ ] `CaptainCapTest`: Officer-Quarters-Cap-Limit

### Docs

- [ ] `crew.md` (neu) — Domain-Overview
- [ ] `decisions.md` Eintrag für T-104a

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
