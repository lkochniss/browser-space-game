# T-039: Passwort-Reset

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** Yes (reset-token-Tabelle oder Felder)
**Depends on:** T-036, T-040

## Description

"Passwort vergessen" Flow. Mail mit signiertem Reset-Link, neues Passwort setzen.

## AC

- [ ] `composer require symfonycasts/reset-password-bundle`
- [ ] `/forgot-password` Form (Email-Eingabe)
- [ ] `/reset-password/{token}` Form (neues PW + Bestätigung)
- [ ] Mail mit signiertem Link, Token-Lifetime z.B. 1h
- [ ] Anti-Enumeration: gleiche Antwort egal ob E-Mail existiert
- [ ] Nach Reset: alle aktiven Sessions invalidieren (Sicherheits-Stempel hochzählen)
- [ ] IT: Reset OK, abgelaufener Token, falsche E-Mail (kein Leak)

## Affected

- Neu: `src/User/Controller/ResetPasswordController.php`
- Neu: Templates für Form + Mail

## Open Questions

1. Auto-Login nach Reset oder normaler Login danach?
2. Notify-Mail an User bei erfolgreichem Reset (Anti-Hijacking)?
