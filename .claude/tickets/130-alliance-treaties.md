# T-130 Allianz-Verträge (MVP: Crusade-Coalition + Resource-Federation)

**Type:** Feature
**Epic:** Multiplayer
**Domain:** User
**Blocked By:** T-052, T-121, T-110
**Status:** Draft
**Effort:** L
**Depends on:** T-052 (Allianz), T-121 (Crusade), T-110 (Trade-Routes)
**Blocks:** —

## Beschreibung
Strukturierte Verträge zwischen Allianzen. MVP: 2 Vertragstypen. Folge-Tickets: Research-Pact, Defense-Coalition.

MVP-Verträge:
- **Crusade-Coalition**: 2+ Allianzen koalieren für gemeinsamen Crusade-Push (T-121). Geteilte Damage-Punkte.
- **Resource-Federation**: 2+ Allianzen können untereinander gratis traden (T-111-Steuer entfällt für Federation-internal Trades).

## Acceptance Criteria
- [ ] AllianceTreaty-Entity (id, type, signatories: Set<AllianceId>, status, signedAt, expiresAt-nullable, terms-JSON)
- [ ] Treaty-Proposal-Workflow: Allianz-Lead schlägt vor → andere Allianzen-Leads müssen approven
- [ ] Crusade-Coalition: bei Crusade-Anmeldung → Damage zählt für alle Coalition-Members
- [ ] Resource-Federation: AuctionService (T-111) prüft Federation → wendet Tax-Discount an
- [ ] Treaty-Bruch: Lead kann kündigen → Cooldown 14d für Re-Treaty
- [ ] Multi-Treaty: Allianz kann mehrere Treaties gleichzeitig
- [ ] Notification an alle Members bei Treaty-Signed

## Affected Tests
- tests/Alliance/Service/TreatyWorkflowTest.php (proposal/approval)
- tests/Alliance/Service/ResourceFederationTaxDiscountTest.php
- tests/Alliance/Service/CrusadeCoalitionDamageSharingTest.php

## Fixtures Needed
Yes — 2-3 Test-Allianzen

## Notes
- Folge-Tickets: Research-Pact (donate RP cross-alliance), Defense-Coalition (gemeinsame Defense vs Outposts)
- Treaty-Bruch-Cooldown verhindert Spam-Treaty-Manipulation
