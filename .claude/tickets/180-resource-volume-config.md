# T-180: Resource/Pop Size-Multiplier-Config (Foundation)

**Type:** Feature (Foundation)
**Status:** Done
**Effort:** S (~1.5-2h)
**Depends on:** —
**Blocks:** T-177, T-178, T-179 (alle nutzen Size-Multi)

## Beschreibung

Zentrale Config für Volume-Multiplier pro Storage-Item-Typ. Foundation für
Generic-Storage-System (T-177-T-179). Definiert "wie viel Lager-Volumen
braucht 1 Einheit Pop / 1 Liter Wasser / 1 kg Eisenerz / 1 m³ Wasserstoff".

## Resolved Decisions

- **Reference-Einheit (Q1):** **1 m³** — physikalische Welt-Einheit.
  Player-intuitiv, kein abstraktes Konzept zu lernen.
- **Multi-Tabelle (Q2):** Vorgeschlagene Tabelle übernehmen (Game-Balance >
  strikte Physik). Detail-Balancing in Folge-Ticket nach Playtest.
- **Konfiguration-Pattern (Q3):** **PHP-Class `ResourceVolumeConfig`** mit
  Const-Map (analog `BuildingCostConfig` / `ShipCostConfig`). Project-
  Convention beibehalten.
- **Pop-Multi-Scope (Q4):** Nur eine Pop-Variante (Working-Pop = 10.0) in
  Foundation. Pop-Tiers (T-089/T-097/T-104) fügen ihre Multi später hinzu.
- **Debug-Tooling (Q5):** Nicht in T-180. Low-Prio Folge-Ticket **T-181**
  legt `app:debug:resource-volume` Command an.

## Multi-Tabelle (Final für Foundation)

| Item | Size-Multi (m³/Unit) | Reasoning |
|------|---------------------|-----------|
| Pop (Working) | 10.0 | 1 Person + Lebensraum |
| Water | 1.0 | Reference (1t ≈ 1m³) |
| Food | 1.2 | Verpackung, Kühlung |
| Oxygen | 0.3 | Gas, komprimiert |
| H2 | 0.2 | Komprimiertes Gas, dichter Storage-Druck |
| Iron-Ore | 2.0 | Sperrig (Brocken mit Luftlücken) |
| Coal | 1.8 | Leichter als Iron-Ore |
| Copper-Ore | 2.0 | Wie Iron-Ore |
| Silicon-Ore | 1.8 | Leichter als Eisen |
| Promethium | 2.5 | Radioaktiv → Bleicontainer |
| Iron-Bar | 1.5 | Refined kompakter als Erz |
| DEBRIS-Resources | 1.0 | Mixed Standard |
| Tier-2 (Steel/Chip/Composite) | 1.0 | Industrieprodukt, kompakt |
| Tier-3 (Antimatter/AI-Core) | 5.0 | Spezial-Containment |

## Acceptance Criteria

- [ ] `App\Resource\Config\ResourceVolumeConfig` Klasse mit Const-Map (final
      Tabelle oben)
- [ ] Static-Lookup-Method `ResourceVolumeConfig::getMultiForResource(ResourceType): float`
- [ ] Static-Lookup-Method `ResourceVolumeConfig::getPopMulti(): float` (10.0)
- [ ] Reference-Einheit "1 m³" in Doc-Block + `resources.md` dokumentiert
- [ ] Tests: Lookup für alle 14 existing ResourceTypes, Pop-Multi
- [ ] Tests: Fail-fast bei unbekanntem Type (Domain-Exception)
- [ ] Doc `resources.md` Volume-Sektion ergänzt
- [ ] Doc `decisions.md` Eintrag: "Storage-Volume in m³, Generic-Storage-
      Vision (T-177ff)"

## Out of Scope

- Storage-Logik selbst (T-177-T-179)
- Pop-Tier-System (T-089/T-097)
- Trade-Pricing pro Volume (T-118)

## Notes

- Foundation-Ticket: muss vor T-177/T-178/T-179 fertig sein
- Volume-Werte sind Balance-Vorschlag; spätere Justierung erwartet
