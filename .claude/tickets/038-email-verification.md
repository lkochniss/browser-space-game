# T-038: E-Mail-Verifizierung

**Type:** Feature
**Epic:** Web Layer & Auth
**Domain:** User
**Blocked By:** T-036, T-040
**Status:** Open
**FX:** No
**MIG:** Yes (verification-token-Felder oder Tabelle)
**Depends on:** T-036, T-040

## Description

Nach Registrierung: Verifizierungs-Mail mit signed URL. Klick → User aktiv. Nutzt `symfonycasts/verify-email-bundle` oder eigene signierte URL.

## AC

- [ ] `composer require symfonycasts/verify-email-bundle`
- [ ] Mail mit Verifizierungs-Link versendet (post-Registrierung)
- [ ] `/verify/email?token=...` Route — verifiziert, setzt `isVerified=true`
- [ ] Resend-Funktion bei abgelaufenem Token
- [ ] Token-Lifetime configurierbar (default 1h)
- [ ] Falls Login ohne Verifikation erlaubt (siehe T-036 Frage): Hinweis-Banner im UI
- [ ] IT: erfolgreiche Verifikation, abgelaufener Token, manipulierter Token, doppelte Verifikation

## Affected

- `src/User/Entity/User.php` (`isVerified` field, evtl. token-fields)
- Neu: `src/User/Controller/EmailVerificationController.php`
- Neu: `templates/email/verify.html.twig`

## Open Questions

1. Vorgehen bei nicht-verifiziertem User: Login blockiert? Limited Access? Voll erlaubt aber mit Banner?
