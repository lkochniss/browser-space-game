# T-086 Schwarzes-Loch-POI

**Type:** Feature
**Status:** Draft
**Effort:** M
**Depends on:** T-019 (POI), T-027 (Planetologie-Forschung)
**Blocks:** —

## Beschreibung
Schwarzes Loch als seltener Galaxy-POI. Hochwertige Tier-3-Resources im Orbit (Antimaterie-Kondensat) — aber gefährlich: Schiffe ohne Schutz-Tech zerstört.

## Acceptance Criteria
- [ ] BlackHolePOI-Entity (systemId, oder eigenes Sub-System, gravityRating, eventHorizonRadius)
- [ ] Galaxy-Init seedet 1-2 Schwarze-Löcher in entfernten Systems
- [ ] Schiff im Event-Horizon ohne Gravity-Shield-Tech: 100% Loss (Permadeath)
- [ ] Mit Gravity-Shield-Tech: Schiff kann Antimaterie-Kondensat ernten (Tier-3-Resource)
- [ ] Ernte-Mechanik: Spezial-Bergungsschiff (T-016 Erweiterung) mit Schwarzes-Loch-Manöver-Tech
- [ ] Long-Cooldown pro Schiff (48h) zwischen Ernte-Versuchen
- [ ] Galaxy-Map (T-160) zeigt Schwarzes Loch mit Warning-Indikator

## Affected Tests
- tests/Movement/Service/BlackHoleHazardTest.php (ship-loss)
- tests/Movement/Service/AntimatterHarvestTest.php (mit Tech)

## Fixtures Needed
Yes — Test-Black-Hole + Test-Schiffe (mit/ohne Tech)

## Notes
- Antimaterie-Kondensat = primäre Quelle für Antimaterie-Resource (T-115)
- Hardcore-Gate: ohne Tech tödlich → Forced-Tech-Investment
