# T-037: Login / Logout (Symfony Security)

**Type:** Feature
**Epic:** Web Layer & Auth
**Domain:** User
**Blocked By:** T-036
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-036

## Description

Form-Login mit Symfony Security Bundle. Remember-me. Logout. Geschützte Game-Routen.

## AC

- [ ] `composer require symfony/security-bundle`
- [ ] `config/packages/security.yaml`: `User` Provider, Form-Login-Authenticator, Logout, Remember-Me, Access-Control für `/game/*` → `IS_AUTHENTICATED_FULLY`
- [ ] `LoginController` `/login` (GET-Form, POST von Security gehandelt)
- [ ] `templates/security/login.html.twig`
- [ ] `/logout` Route
- [ ] Remember-Me-Checkbox
- [ ] Brute-Force-Throttle (Symfony LoginThrottling)
- [ ] Last-Login-Timestamp auf User
- [ ] IT: Login OK, Login mit falschem PW, Login als unverifizierter User (Vorgehen abh. T-036 Frage)

## Affected

- `config/packages/security.yaml`
- Neu: `src/User/Controller/LoginController.php`
- Neu: `templates/security/login.html.twig`
- `src/User/Entity/User.php` (lastLoginAt)

## Open Questions

1. Login-Throttle-Limits? Vorschlag: 5 Versuche / 15 min pro IP+User.
2. Remember-Me-Lifetime? Vorschlag: 14 Tage.
