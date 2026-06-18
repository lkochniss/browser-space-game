# T-041: User-Profil + Einstellungen

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-037

## Description

Eingeloggter User braucht Settings-Page: E-Mail ändern (mit Verifikation), Passwort ändern, Notification-Präferenzen.

## AC

- [ ] `/settings` (auth required)
- [ ] Form: E-Mail ändern → triggert Re-Verification mit neuer Adresse
- [ ] Form: Passwort ändern → braucht aktuelles Passwort
- [ ] Form: Notification-Präferenzen (T-053 hängt dran)
- [ ] CSRF auf allen Forms
- [ ] IT: Passwort-Wechsel OK, falsches aktuelles PW, E-Mail-Wechsel re-trigger Verify

## Affected

- Neu: `src/User/Controller/SettingsController.php`
- Neu: `src/User/Form/ChangePasswordType.php`, `ChangeEmailType.php`
- Neu: `templates/settings/*.html.twig`

## Open Questions

1. E-Mail-Wechsel: alte Adresse als Notify (Anti-Hijack) oder nur neue verifizieren?
