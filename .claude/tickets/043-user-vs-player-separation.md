# T-043: User vs Player Trennung

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** Yes (Player.userId FK)
**Depends on:** T-036

## Description

Aktuell `Player` ist Game-Entity. Mit Auth (T-036) kommt `User` als Account. Klare Trennung:

- **User** = Account/Login (E-Mail, PW, Roles, Status)
- **Player** = Spielfigur (Spielername, Planeten, Forschung, Score)

1:1-Beziehung. User kann (in DSGVO-Mode) gelöscht werden, Player bleibt anonymisiert für Spielwelt-Konsistenz (T-042).

## AC

- [ ] `Player` referenziert `User` (FK userId)
- [ ] `Player.displayName` separat (nicht E-Mail)
- [ ] `CreateNewPlayerCommand` (existing) erweitert: brauch User als Input
- [ ] Player-Erzeugung im Onboarding-Flow (T-046), nicht bei Registrierung
- [ ] `User::getPlayer()` Helper
- [ ] Migration für FK + displayName-Field

## Affected

- `src/Player/Model/Player.php` (User-Ref + displayName)
- `src/Player/Command/CreateNewPlayerCommand.php`
- `src/Player/Service/CreateNewPlayerService.php`

## Open Questions

1. 1 User = 1 Player oder mehrere Player pro User möglich (Multi-Char-Mode)? Vorschlag: 1:1, KISS.
2. DisplayName-Eindeutigkeit: global unique?
