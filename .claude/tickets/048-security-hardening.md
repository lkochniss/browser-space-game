# T-048: Security-Hardening (Health + Headers + Rate-Limit)

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No

## Description

Standard-Hygiene für Browser-Game:

1. Health-Check für Monitoring/Loadbalancer
2. Security-Response-Headers (CSP, HSTS, X-Frame-Options, …)
3. Rate-Limiter auf sensiblen Endpoints (Login, Register, Reset, API)
4. CSRF auf allen state-changing Forms (sollte default sein, verifizieren)

## AC

- [ ] `/health` und `/ready` Endpoints (DB-Check, Tick-Check, evtl. Mailer-Check)
- [ ] Security-Headers via Listener oder NelmioSecurityBundle (CSP, HSTS, X-Frame-Options=DENY, X-Content-Type-Options=nosniff, Referrer-Policy)
- [ ] `composer require symfony/rate-limiter`
- [ ] Limiter: Registration (5/h pro IP), Login (siehe T-037), Reset (3/h pro IP), Email-Resend (3/h pro User)
- [ ] CSRF-Audit aller Forms — sicherstellen dass `csrf_protection: true`
- [ ] IT: Rate-Limit-Trigger auf Login

## Affected

- Neu: `src/Health/Controller/HealthController.php`
- Neu: `config/packages/security_headers.yaml` oder `EventListener`
- `config/packages/rate_limiter.yaml`

## Open Questions

1. CSP-Strictness: nur self + Tailwind-Inline, oder volle nonce-Strategie?
2. NelmioSecurityBundle vs eigener Listener? Bundle ist gepflegt — bevorzugt.
