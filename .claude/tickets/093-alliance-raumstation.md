# T-093 Allianz-Raumstation (T-023 Erweiterung)

**Type:** Feature
**Epic:** Multiplayer
**Domain:** POI
**Blocked By:** T-023, T-052
**Status:** Draft
**Effort:** L
**Depends on:** T-023 (Raumstation), T-052 (Allianz)
**Blocks:** —

## Beschreibung
Allianz-built Raumstation pro System. Kollektive Bauressourcen-Beiträge der Mitglieder. Bietet Allianz-spezifische Services.

Services:
- Repair-Hub für Schiffe (heilt nach Battle)
- Trade-Bonus für Allianz-Members im System (-50% Trade-Steuer)
- Defense-Bonus für Verbündete-Schiffe in System
- Crusade-Coalition-Sammelpunkt (T-130)

## Acceptance Criteria
- [ ] AllianceStation-Entity extends Station (T-023), allianceId-FK
- [ ] Max 1 Allianz-Station pro System
- [ ] Bau-Kosten enorm (Beispiel: 100k Steel, 50k Hull-Plate, 20k Chip) — Member donieren
- [ ] Kontribution-Tracking: pro Donor pro Resource akkumuliert
- [ ] Build-Progress sichtbar für alle Allianz-Members
- [ ] Services aktivieren erst nach Vollständigkeit
- [ ] Station zerstörbar: Renegade-Faction-Outpost-Mechanik (T-075) kann Allianz-Station angreifen
- [ ] Beim Verlust: Members verlieren keine Donations, aber Station weg

## Affected Tests
- tests/Alliance/Service/StationConstructionTest.php (donation flow)
- tests/Alliance/Service/StationServicesTest.php (repair, trade-bonus)

## Fixtures Needed
Yes — Allianz mit Members + Donations

## Notes
- Anti-Crush: Stationen können fallen, aber Members behalten ihre Heimat-Planets
- Konsistent mit "keine Allianz-Bank"-Decision: Donation = Beitrag-zu-Build, nicht zentraler Pool
