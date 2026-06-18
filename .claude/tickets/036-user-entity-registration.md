# T-036: User-Entity + Registrierung

**Type:** Feature
**Status:** Open
**FX:** Yes (Demo-User)
**MIG:** Yes
**Depends on:** TD-031, TD-032 (Persistence + Tests)

## Description

Game braucht Accounts. `User` ≠ `Player` (T-043). User = Auth-Identität (E-Mail + Passwort + Status). Registrierungsformular legt User an, sendet Verifizierungs-Mail (T-038).

## AC

- [ ] Neue Domain `src/User/`
- [ ] `User` Entity (id UUID, email, password-hash, roles, isVerified, createdAt) — implementiert `UserInterface` + `PasswordAuthenticatedUserInterface`
- [ ] Doctrine-Mapping + Migration
- [ ] `RegistrationFormType` (Email + Passwort + Passwort-Bestätigung + AGB-Checkbox)
- [ ] `RegistrationController` (`/register` + POST-Handler)
- [ ] Validator: E-Mail eindeutig, Passwort min-Länge (12+), Passwort-Strength-Check
- [ ] CSRF-Token im Formular
- [ ] User wird angelegt mit `isVerified=false`
- [ ] Verifizierungs-Mail dispatched (Hook für T-038)
- [ ] IT: Registrierung happy-path + Duplikat-E-Mail + zu schwaches Passwort

## Affected

- Neu: `src/User/Entity/User.php`, `Repository/UserRepository.php`, `Form/RegistrationFormType.php`, `Controller/RegistrationController.php`
- Neu: `templates/registration/register.html.twig`
- Neu: Migration

## Open Questions

1. E-Mail-Verifikation Pflicht vor Login (T-038), oder Login möglich aber eingeschränkt?
2. Username zusätzlich zu E-Mail? Vorschlag: nur E-Mail + Spielername wird in Player (T-043) gesetzt.
3. AGB/Datenschutz-Checkbox erforderlich (DE-Recht) — siehe T-050.
