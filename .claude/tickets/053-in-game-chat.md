# T-053: In-Game-Chat / Messaging

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** Yes (Conversation + Message Tabellen)
**Depends on:** T-043, T-052

## Description

Multiplayer-Feature. Spieler-zu-Spieler-Direkt-Nachricht + Allianz-Chat. **Setzt Multiplayer-Modus voraus.**

## AC

- [ ] `Conversation` Entity (DM 1:1, AllianceChat 1:N)
- [ ] `Message` Entity (sender, content, sentAt, readBy)
- [ ] DM-Inbox-View `/messages`
- [ ] Allianz-Chat in Allianz-View
- [ ] Polling-basierter Refresh (oder Mercure für Realtime — T-045 Open Question)
- [ ] Message-Limits gegen Spam (Rate-Limit, T-048)
- [ ] Block-User-Funktion
- [ ] Inhalts-Filter (basic, gegen Beleidigungen) optional
- [ ] IT: DM senden/lesen, Block-Funktion

## Affected

- Neu: `src/Messaging/` Domain
- Neu: `src/Messaging/Controller/`
- evtl. `composer require symfony/mercure-bundle` (falls Realtime)

## Open Questions

1. Single vs Multi → wenn Single, kein Chat nötig.
2. Realtime via Mercure/SSE oder Polling reicht? Polling = einfach, Mercure = nice-to-have.
3. Moderation/Reporting nötig?
