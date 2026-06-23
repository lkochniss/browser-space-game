# T-161 Notifications (in-game / PWA Web-Push)

**Type:** Feature
**Epic:** Game UI
**Domain:** UI
**Blocked By:** T-034, T-047
**Status:** Draft
**Effort:** L
**Depends on:** T-034 (Web-Layer), T-047 (In-Game-Notifications-Foundation)
**Blocks:** —

## Beschreibung
Multi-Channel-Notification-System. In-Game-Bell-Icon + optional Web-Push (PWA) für offline-Spieler.

Trigger:
- Battle-Result (T-103)
- Quest-Complete (T-120)
- Allianz-Treaty-Proposal (T-130)
- Crusade-Start (T-121)
- Auction-Match (T-111)
- Rescue-Request (T-153)
- Outpost-Attack-Warnung (T-068 Sensor)

## Acceptance Criteria
- [ ] Notification-Entity (id, playerId, type, payload-JSON, status: UNREAD/READ, createdAt)
- [ ] EventListener-System: Domain-Events emittieren Notifications
- [ ] In-Game-Bell-Icon mit Counter (UNREAD)
- [ ] Notification-Dropdown: zeigt letzte 20, Mark-Read on click
- [ ] Optional Web-Push: bei Player-Opt-In via Service-Worker (PWA)
- [ ] Push nur für Critical-Events (Heimat-Attack, Crusade-Start) — nicht Spam
- [ ] Notification-Preferences pro Player: opt-in/out per Type
- [ ] Bulk-Mark-Read API

## Affected Tests
- tests/Notification/Service/NotificationDispatchTest.php (multiple types)
- tests/Notification/Service/PushOptInTest.php

## Fixtures Needed
Yes — Player + Notification-Preferences

## Notes
- T-047 (existing ticket) ist In-Game-Foundation — dieses Ticket erweitert um Web-Push + Multi-Type
- PWA-Opt-In: Browser-Permission, Service-Worker-Registration
- Notification-Type-Filter verhindert Spam (z.B. Daily-Quest-Complete optional silent)
