# T-106 Diplomatic-Buildings (Embassy / Comm-Array / Cultural-Mission)

**Type:** Feature
**Status:** Draft
**Effort:** M (TBD)
**Depends on:** T-073 (Faction), T-052 (Allianz), T-104c (Diplomat-Crew)
**Blocks:** —

## Beschreibung

Diplomatie-Spezial-Buildings. Komplementiert T-104c Diplomat-Crew + Specialist-Track Diplomacy (T-098).

Neue Buildings:
- EMBASSY: pro Faction max 1; +Reputation-Speed (×1.3) zur jeweiligen Faction; Voraussetzung für T-078 Faction-Quests
- COMM_ARRAY: Galaxy-weite Notification-Reichweite (T-161); Allianz-Coordination-Speed (T-130 Treaty-Approval-Time -50%)
- CULTURAL_MISSION: +Pop-Loyalty (zur eigenen Faction-Affiliation T-122 Background); Anti-Renegade-Drift
- INTELLIGENCE_HQ: erweitert T-131 Spy-Network — größere Spy-Cap, schnelleres Spy-Training
- TRANSLATOR_BUREAU: ermöglicht Xenos-Faction-Reputation-Aufbau (T-073 Override für ALWAYS_HOSTILE-Lock auf Xenos? Decision needed)

## Acceptance Criteria

- [ ] TBD: Neue BuildingType-Werte
- [ ] TBD: Embassy-Reputation-Multiplier in ReputationService (T-073)
- [ ] TBD: Comm-Array-Notification-Range integriert
- [ ] TBD: Cultural-Mission-Loyalty-Mechanik (verbindet zu Player-Background T-122)
- [ ] TBD: Translator-Bureau Decision: Xenos-Reputation-Path öffnen oder gesperrt lassen?

## Open Questions

- Embassy pro Faction: kann Spieler 4 Embassies parallel haben (eine pro Faction)?
- Anti-Crush-Side-Effekt von Cultural-Mission: kein Spieler-Damage, nur Pop-Effekt — passt
- Translator-Bureau bricht Game-Lore (Xenos = always hostile) — Decision-Required

## Notes

- Diplomatie-Layer wird zur eigenen strategischen Tiefe
- Ohne diese Buildings ist Diplomatie-Track (T-098) thinly featured
