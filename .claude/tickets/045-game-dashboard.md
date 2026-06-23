# T-045: Game-Dashboard (Hauptansicht)

**Type:** Feature
**Epic:** Game UI
**Domain:** UI
**Blocked By:** T-035, T-037, T-043
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-035, T-037, T-043

## Description

Eingeloggter Spieler braucht Übersicht: aktueller Planet, Resources, Buildings, Forschung, Flotten, nächster Tick. Hauptview unter `/game` oder `/play`.

## AC

- [ ] `GameDashboardController` `/game`
- [ ] Anzeige: Aktiver Planet (Resources, Pop, Buildings)
- [ ] Tab-Nav: Planet | Forschung | Flotte | Galaxie
- [ ] Stimulus-Auto-Refresh (Polling alle 30s) für Resource-Counter
- [ ] Tick-Countdown bis zum nächsten Tick
- [ ] Responsive Tailwind-Layout

## Affected

- Neu: `src/Game/Controller/GameDashboardController.php`
- Neu: `templates/game/dashboard.html.twig`
- Neu: `assets/controllers/auto_refresh_controller.js`

## Open Questions

1. Default-Tab beim Login? Vorschlag: Planet.
2. Polling oder SSE/WebSocket (Mercure)? Vorschlag: Polling reicht für Browser-Game.
3. Multi-Planet-Auswahl: Tab-Bar pro Planet oder Dropdown?
