# T-051: Logging + Monitoring Setup

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No

## Description

Strukturierte Logs + Monitoring-Hooks. Per-Domain-Channels. Optional Sentry für Errors.

## AC

- [ ] `composer require symfony/monolog-bundle` (sollte Symfony-Default sein, verifizieren)
- [ ] Channels: `tick`, `auth`, `battle`, `mailer`, `payment` (falls je relevant)
- [ ] Log-Format: JSON in Prod (für ELK/Grafana-Loki), Text in Dev
- [ ] Korrelations-ID pro Request (Middleware) — propagiert in Async-Messages (T-044 falls Messenger)
- [ ] Konfigurierbares Sentry-DSN via `.env` (`composer require sentry/sentry-symfony`, optional)
- [ ] Doku in README.md: wie Logs gelesen werden

## Affected

- `config/packages/monolog.yaml`
- Neu: `src/Common/EventListener/CorrelationIdListener.php`
- evtl. `composer.json`

## Open Questions

1. Sentry oder vergleichbar (Bugsnag, GlitchTip) — oder weglassen für MVP?
2. Log-Storage Prod: lokal Datei + logrotate, oder Cloud-Log-Service?
