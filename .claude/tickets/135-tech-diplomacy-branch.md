# T-135 Forschungs-Branch: Diplomatie

**Type:** Feature
**Status:** Draft
**Effort:** L (TBD)
**Depends on:** T-025, T-073 (Faction), T-104c (Diplomat-Crew), T-106 (Diplomatic-Buildings)
**Blocks:** —

## Beschreibung

Tech-Branch für Diplomacy-Specialist-Track (T-098). Optimiert Reputation, Trade, Alliance.

Tech-Tree (Tier 1-5):

**Tier 1**: Cultural-Studies (Reputation-Speed +10%), Trade-Treaty-Basics (Auction-Tax -5%)
**Tier 2**: Embassy-Tech (T-106 Embassy-Effekt verstärkt), Comm-Network (T-106 Comm-Array-Range +50%)
**Tier 3**: Negotiation-Tactics (Treaty-Approval -50% time T-130), Cultural-Influence (Pop-Loyalty-Boost via T-106 Cultural-Mission)
**Tier 4**: Galactic-Standing (Influence-Punkte-Akkumulation +25% T-084), Universal-Translator (Xenos-Reputation-Path open — Decision T-106 connecten)
**Tier 5**: Pax-Imperialis (alle Allianz-Treaties haben +10% Effekt, Crusade-Coalition Damage +20%), Faction-Mediator (kann Inter-Faction-Reputation-Trades initiieren — neue Mechanik)

## Acceptance Criteria

- [ ] TBD: 10 ResearchNodes
- [ ] TBD: Reputation-Multiplier-Integration mit ReputationService (T-073)
- [ ] TBD: Treaty-Speed-Effekt (T-130)
- [ ] TBD: Tier-4 Universal-Translator → Decision zu Xenos-Hostile-Override
- [ ] TBD: Faction-Mediator (Tier 5) als neue Cross-Faction-Trade-Mechanik

## Open Questions

- Universal-Translator als Tech-Choice (öffnet Xenos-Diplomatie) — bricht das Game-Lore?
- Pax-Imperialis-Magnitude: zu OP für Allianz-Crusade?
- Faction-Mediator: konkrete Mechanik?

## Notes

- Diplomacy-Specialist-Track-Identität
- Verbindet alle Diplomatic-Buildings (T-106) + Diplomat-Crew (T-104c) + Allianz-Treaties (T-130)
