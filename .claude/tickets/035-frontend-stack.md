# T-035: Frontend-Stack — Tailwind + Stimulus + AssetMapper

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-034

## Description

Stack-Vorgabe: Stimulus + Tailwind. Aktuell nichts davon installiert. Recipe-basiert aufsetzen.

## AC

- [ ] AssetMapper-Bundle (`composer require symfony/asset-mapper symfony/asset symfony/stimulus-bundle`)
- [ ] Tailwind via `symfony/ux-tailwind` oder `symfonycasts/tailwind-bundle`
- [ ] `assets/app.js` + Stimulus-Controllers Folder (`assets/controllers/`)
- [ ] `assets/styles/app.css` mit Tailwind-Direktiven
- [ ] `base.html.twig`: `{{ importmap('app') }}` + Tailwind-CSS
- [ ] Erstes Demo-Stimulus-Controller (`hello_controller.js`) auf Home rendern
- [ ] `bin/console tailwind:build` läuft, `importmap:install` läuft
- [ ] Doku in README.md (Dev-Server, Asset-Build)

## Affected

- `composer.json`
- Neu: `assets/`
- `templates/base.html.twig`

## Open Questions

1. AssetMapper (no Node) vs Webpack Encore (Node)? Vorschlag: AssetMapper — keine Node-Dep, Symfony 7 default. Tailwind-Bundle compiled CSS lokal.
