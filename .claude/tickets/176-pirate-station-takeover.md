# T-176: Pirate-Takeover ABANDONED → Pirate-Faction + Combat-Capture

**Type:** Feature
**Status:** Draft (Decisions pending)
**Effort:** M-L (~3-4h, abhängig von T-103-Foundation)
**Depends on:** T-023b (ABANDONED-State), T-073 (Faction-Foundation), T-103 (Battle-Engine — Draft)
**Blocks:** —

## Beschreibung

Zwei Mechaniken zusammen, beide rund um Pirate-Station-Interaktion:

1. **Pirate-Takeover ABANDONED:** Wenn eine ABANDONED-Station nicht binnen
   Zeit X geclaimed wird, kann Pirate-Faction (T-073) sie übernehmen. Status
   wechselt zurück auf ACTIVE, owner=PirateFaction, Pop wird neu gesetzt.
   Player muss dann via Combat-Capture rein (Mechanik #2).
2. **Combat-Capture:** Player kann Pirate-Owned-Stations (von T-175 oder via
   Pirate-Takeover) angreifen. Bei erfolgreicher Schlacht (T-103 Battle-Engine)
   wechselt Owner zum Player. Storage bleibt erhalten (Loot-Bonus).

## Open Questions

### Q1: Pirate-Takeover-Timing

Wie lange bleibt ABANDONED claimable, bevor Pirate übernimmt?

- (a) 7 Tage
- (b) 14 Tage
- (c) 30 Tage
- (d) Variabel: Random innerhalb 7-30 Tage, deterministisch via Station-ID-Seed

### Q2: Takeover-Wahrscheinlichkeit

- (a) Garantiert nach Timer-Ablauf (100%)
- (b) Per Tick-Check: nach Timer-Ablauf X% Chance pro Tick
- (c) Abhängig von Pirate-Threat-Level der Region (T-099 Threat-Skalierung)

### Q3: Initial-Pop bei Pirate-Takeover

- (a) Analog T-175 Initial-Pop (z.B. 500 Pirate-Garrison)
- (b) Niedriger (z.B. 100) — Pirate gerade erst eingezogen
- (c) Wie zum Zeitpunkt des Death-Events (Pop-State frozen)

### Q4: Storage bei Pirate-Takeover

- (a) Storage bleibt unverändert (Pirate erbt was da war)
- (b) Pirate-Loot-Addition: zufälliges Add (Plunder mitgebracht)
- (c) Storage wird genullt (Pirate hat erstmal nichts)

### Q5: Combat-Capture Trigger

- (a) Spezial-Command: `RaidStationCommand(fleetId, stationPoiId)` —
  separate Action, eigenes Combat-Setup
- (b) Generisch via T-103 Battle: Fleet-Move zu System mit Pirate-Station
  triggert Battle automatisch (wie Inter-Fleet-Combat)
- (c) Combination: Fleet muss explizit dock-attempt machen → Battle

### Q6: Combat-Capture-Side-Effects

- (a) Storage 100% behalten (Player-Loot voll)
- (b) Storage X% beschädigt im Combat (z.B. -25%)
- (c) Pop wird komplett ausgelöscht; Player muss Initial-Pop nachsenden
- (d) Combination: Pop ausgelöscht + Storage minus Loss-%

### Q7: Pirate-Bounty / Reputation-Effekt

- Combat-Capture gegen Pirate-Faction = Reputation-Hit gegen Pirates (T-073)?
- Bei Erfolg: Reputation-Penalty bei Pirates, evtl. Reputation-Gain bei
  Renegade/MerchantGuild?

### Q8: Re-Takeover möglich?

Was passiert wenn Player Capture-Station verliert (z.B. Pop stirbt durch
Maintenance-Mangel → ABANDONED)?

- (a) Standard ABANDONED-Loop (Q1-Q2 gilt wieder, evtl. Pirate übernimmt
  irgendwann erneut)
- (b) Pirate hat "Memory" — schnellere Re-Takeover bei vorherigem Verlust
- (c) Nicht relevant — wenn Player verliert, Station behält ABANDONED-Status
  bis nächste Claim/Capture-Aktion

## Acceptance Criteria (Draft — final nach Q1-Q8)

### Pirate-Takeover
- [ ] `StationMaintenanceService` (T-023b) erweitert um Takeover-Check
- [ ] Timer-Persistenz: Station hat `abandonedAt` Timestamp (Q1, Q2)
- [ ] Takeover-Effekt: owner=Pirate-Faction, status=ACTIVE, Pop-Initial (Q3),
      Storage-Behavior (Q4)
- [ ] Event: `StationTakenOverByPirateEvent` für Logging/Notification

### Combat-Capture
- [ ] Capture-Command oder Battle-Auto-Trigger (Q5)
- [ ] Capture-Effekt: owner=Player, Storage-Behavior (Q6), Pop-Reset (Q6)
- [ ] Reputation-Effekt gegen Faction (Q7)
- [ ] Event: `StationCapturedEvent`

### Tests
- [ ] Takeover-Tick wenn Timer abgelaufen
- [ ] Player-Claim verhindert Pirate-Takeover wenn rechtzeitig
- [ ] Combat-Capture-Flow (mock T-103-Battle-Outcome)
- [ ] Reputation-Update korrekt

### Doku
- [ ] `poi.md` Station-Section um Takeover/Capture erweitert
- [ ] `factions.md` Reputation-Effekt dokumentiert

## Out of Scope

- T-103 Battle-Engine selbst (Foundation-Folge)
- Pirate-Threat-Skalierung dynamisch (T-099)
- Allianz-Coordinated-Capture (T-093 Allianz-Stations)
- Alliance-Defense-Coalition (T-133)

## Notes

- T-103 ist heute Draft — T-176 kann teilweise vor T-103 entwickelt werden
  (Takeover-Mechanik), aber Combat-Capture wartet auf T-103
- Realistisch: T-176 in zwei Stages bauen
  - Stage 1: Pirate-Takeover (kein T-103 nötig)
  - Stage 2: Combat-Capture (blocked-by T-103)
