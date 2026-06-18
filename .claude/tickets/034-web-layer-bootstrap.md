# T-034: Symfony Web-Layer Bootstrap

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No

## Description

Aktuell nur CLI-Sim. `templates/base.html.twig` minimal, `src/Controller/` existiert nicht (obwohl `routes.yaml` darauf verweist). Vor jeder UI-Arbeit braucht das Projekt einen Web-Layer-Grundstock: Layout, Error-Pages, Home/Landing-Controller.

## AC

- [ ] `src/Controller/` existiert
- [ ] `HomeController` mit `/` Route → einfache Landing-Page
- [ ] `templates/base.html.twig` aufgewertet: Header, Nav-Stub, Footer-Slot, Flash-Messages-Block
- [ ] Custom Error-Templates (`templates/bundles/TwigBundle/Exception/`): 404, 500, 403, 503 (Maintenance)
- [ ] Routing-Setup verifiziert (`routes.yaml`, `routes/` falls genutzt)
- [ ] `bin/console debug:router` zeigt Routes
- [ ] `dev`-Server (`symfony serve` oder `php -S`) liefert Landing aus

## Affected

- Neu: `src/Controller/HomeController.php`
- `templates/base.html.twig`
- Neu: `templates/home/index.html.twig`
- Neu: `templates/bundles/TwigBundle/Exception/error*.html.twig`

## Open Questions

1. AssetMapper oder Webpack Encore? (T-035 entscheidet — empfohlen: AssetMapper, Symfony 7 default)
2. Locale-Strategie: nur DE, oder DE+EN von Anfang an? (T-056 i18n)
