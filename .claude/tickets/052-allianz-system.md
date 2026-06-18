# T-052: Allianz-System

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** Yes (Alliance-Tabelle + Membership)
**Depends on:** T-043, T-024

## Description

Multiplayer-Feature. Spieler schließen sich zu Allianzen zusammen — gemeinsame Verteidigung, Diplomatie, Chat. **Setzt Multiplayer-Modus voraus** (offene Architektur-Frage, siehe TD-032 / T-007).

## AC

- [ ] `Alliance` Entity (id, name, tag, founder, createdAt, description)
- [ ] `AllianceMembership` (player, alliance, role, joinedAt)
- [ ] Roles: `LEADER`, `OFFICER`, `MEMBER`
- [ ] Commands: `CreateAlliance`, `InvitePlayer`, `AcceptInvite`, `LeaveAlliance`, `KickMember`, `PromoteMember`
- [ ] Allianz-Mitglieder gelten in `BattleResolver` (T-024) als verbündet (kein Auto-Combat)
- [ ] UI: Allianz-Übersicht, Mitgliederliste, Invitations-Inbox
- [ ] IT: Gründung, Beitritt, Verlassen, Auflösung bei letztem Mitglied weg

## Affected

- Neu: `src/Alliance/` Domain
- `src/Battle/Service/BattleResolver.php` (Allianz-Check)

## Open Questions

1. **Single-Player vs Multi-Player Game-Mode?** Wenn Single → dieses Ticket entfällt.
2. Allianz-Cap (max Mitglieder)?
3. NAP/Krieg-Diplomatie jetzt oder eigenes Ticket?
