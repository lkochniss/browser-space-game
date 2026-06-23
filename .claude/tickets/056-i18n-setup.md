# T-056: i18n Setup (DE + EN)

**Type:** Feature
**Epic:** Web Layer & Auth
**Domain:** Common
**Blocked By:** None
**Status:** Open
**FX:** No
**MIG:** No

## Description

Symfony Translator + locale-Switching. Initial DE + EN. Translation-Files für UI-Strings.

## AC

- [ ] `translations/messages.de.yaml`, `messages.en.yaml`
- [ ] Default-Locale + Fallback in `config/packages/translation.yaml`
- [ ] Locale-Switcher im Header (Stimulus-Controller)
- [ ] Locale persistiert in User-Settings (T-041) und Session
- [ ] Routes mit `_locale`-Prefix oder Locale-Subdomain — entscheiden
- [ ] Domain-spezifische Texte (z.B. Resource-Namen) übersetzt
- [ ] CI-Check: keine Hardcoded-Strings in Templates (Linter optional)

## Affected

- Neu: `translations/`
- `config/packages/translation.yaml`
- `templates/base.html.twig` (Locale-Switcher)

## Open Questions

1. URL-Strategie: `/de/...` Prefix, Subdomain, oder unsichtbar via Header? Vorschlag: Prefix.
2. Game-Lore/Doc-Texte (Erkundung-Beschreibungen, etc.) auch übersetzen — Aufwand-Tradeoff.
3. Plural-/Gender-Handling (ICU)?
