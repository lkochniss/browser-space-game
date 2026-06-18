# T-040: Mailer-Setup

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No

## Description

`symfony/mailer` für Auth-Mails (Registrierung, Reset). Dev: lokaler Mailcatcher (Mailpit) im Docker-Compose. Prod: SMTP / Provider via DSN.

## AC

- [ ] `composer require symfony/mailer`
- [ ] `MAILER_DSN` in `.env` + `.env.dev` + Beispiele
- [ ] Docker-Compose: Mailpit-Container (Port 8025) + DSN für Dev
- [ ] Base-Mail-Layout (`templates/email/base.html.twig`)
- [ ] `From`-Adresse zentral in `.env` + Service
- [ ] Funktioneller Test: TestMailer in PHPUnit (`MAILER_DSN=null://null` oder `assertEmail*`)

## Affected

- `.env`, `.env.dev`
- `docker-compose.yaml` (Mailpit)
- `config/packages/mailer.yaml` (default ok)
- Neu: `templates/email/base.html.twig`

## Open Questions

1. Provider für Prod? Mailgun / Postmark / SES? Kann später entschieden werden, DSN ist abstrakt.
