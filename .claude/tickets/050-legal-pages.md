# T-050: Legal-Pages — Impressum / Datenschutz / ToS / Cookie-Banner

**Type:** Feature
**Epic:** Web Layer & Auth
**Domain:** UI
**Blocked By:** None
**Status:** Open
**FX:** No
**MIG:** No

## Description

DE-Recht: Browser-Game braucht Impressum, Datenschutzerklärung, AGB/ToS. Cookie-Banner für nicht-essenzielle Cookies (DSGVO/TTDSG).

## AC

- [ ] Static-Routen `/imprint`, `/privacy`, `/terms`
- [ ] Twig-Templates mit Platzhalter-Inhalten (echte Texte vom User/Anwalt)
- [ ] Footer-Links überall (in `base.html.twig`)
- [ ] Cookie-Banner-Stimulus-Controller: Opt-in für nicht-essenzielle Cookies; Storage in localStorage
- [ ] Verifikation: keine nicht-essenziellen Cookies/Tracker vor Opt-in geladen
- [ ] AGB-Akzeptanz bei Registrierung (T-036) Pflicht-Checkbox

## Affected

- Neu: `src/Legal/Controller/LegalController.php`
- Neu: `templates/legal/*.html.twig`
- Neu: `assets/controllers/cookie_banner_controller.js`
- `templates/base.html.twig` (Footer, Banner-Slot)

## Open Questions

1. Hosted ToS-Generator-Service (z.B. iubenda) oder selbst-hosted Texte?
2. Tracking-Tools überhaupt geplant? (Wenn nein, Banner-Aufwand minimal.)
