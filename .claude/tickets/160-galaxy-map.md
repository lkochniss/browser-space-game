# T-160 Galaxie-Map (Canvas + Stimulus oder SVG)

**Type:** Feature
**Epic:** Game UI
**Domain:** UI
**Blocked By:** T-034, T-035, T-087
**Status:** Draft
**Effort:** XL
**Depends on:** T-034 (Web-Layer), T-035 (Frontend-Stack), T-087 (Fog-of-War)
**Blocks:** —

## Beschreibung
Interaktive Galaxie-Karte. Spieler sieht Sektoren/Systems, kann pannen/zoomen, klickt Systems für Detail-View, plant Schiff-Movement.

**Tech-Decision pending**: Canvas (mehr Performance, custom Drawing) vs SVG (DOM-easier, accessibility) — beide Stimulus-controlled. Empfehlung: SVG für MVP, Canvas-Pivot bei Performance-Issues.

## Acceptance Criteria
- [ ] Map-Controller (Stimulus) lädt Galaxy-State per AJAX
- [ ] Render: Systems als Knoten, Verbindungen optional
- [ ] Fog-of-War-Integration (T-087): nur entdeckte Systems sichtbar
- [ ] Interaktion: Pan, Zoom, Click-System → Detail-View
- [ ] Schiff-Routes als Linien zwischen Source/Target
- [ ] POIs (Wormhole T-085, Black-Hole T-086, Outposts T-075) mit Icons
- [ ] Performance: rendert 1000+ Systems flüssig
- [ ] Mobile-tauglich (Pinch-Zoom, Tap)

## Affected Tests
- tests/Map/Controller/GalaxyMapControllerTest.php (DTO-API)
- E2E: Cypress / Playwright für Stimulus-Interaktion (sobald E2E-Setup existiert)

## Fixtures Needed
Yes — größere Test-Galaxy mit Systems + Routen

## Notes
- Empfehlung: SVG MVP. Canvas falls Perf-Issues bei großen Galaxies (10k+ Systems)
- Tech-Choice in eigenem ADR dokumentieren bevor Implementation
- Map-Click → Routes via T-017 Flotte-Movement
