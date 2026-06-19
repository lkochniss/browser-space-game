# T-064: Bauzeit-Speed-Boost via Forschung + Buildings

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No (computed multiplier; keine neuen Spalten)
**Depends on:** T-062 ✓ (Echtzeit-Bauzeit), T-025/T-026 (Forschungs-Framework)

## Beschreibung

User-Vision (T-062-Klärung): "Es gibt später über Forschung und Gebäude die Möglichkeit, die Bau-Geschwindigkeit zu erhöhen". T-062 etabliert harte Bauzeiten (`baseDuration × 2^currentLevel`). T-064 fügt Multiplikatoren hinzu, die diese Zeit reduzieren.

## Scope

- Forschungs-Tech: senkt Bauzeit global um X% pro Level
- Spezial-Buildings (z.B. "Construction Hub" / "Engineering Lab"): senken Bauzeit lokal pro Planet
- Effektive Duration: `effectiveDuration = baseDuration × Π(multipliers)`

## Acceptance Criteria (Draft)

- [ ] `BuildingDurationConfig::getDurationSeconds()` erweitert um Planet- und/oder Player-Context für Multiplier-Berechnung
- [ ] Neuer `ConstructionSpeedMultiplierService` aggregiert:
  - Active Forschungs-Multiplier (T-025/T-026 dependency)
  - Active Building-Multiplier auf Planet (z.B. 1 Construction Hub L3 = -30%)
- [ ] Neuer BuildingType (z.B. `CONSTRUCTION_HUB`) mit Speed-Beitrag
- [ ] `BuildBuildingCommandService` + `UpgradeBuildingCommandService` nutzen den effektiven Wert
- [ ] Multiplier sind kumulativ, aber gecapped (z.B. min. 10% baseDuration; nie 0)
- [ ] Tests:
  - Multiplier wird korrekt aggregiert
  - finishedAt entspricht reduzierter Duration
  - Cap auf min. 10% greift bei extremen Stacks

## Open Questions

1. Multiplikator-Stacking: multiplikativ (0.9 × 0.8 × 0.7) oder additiv (1 - 0.1 - 0.2 - 0.3)?
2. Speed-Tech Forschungs-Branch im Tree (T-025) wo angesiedelt?
3. Sollen aktiv laufende Bauten retroaktiv von neuer Forschung profitieren oder nur neue Bauten?
4. Effekt auf Upgrades genauso wie Initial-Bauten?

## Affected

- Neu: `src/Building/Service/ConstructionSpeedMultiplierService.php`
- Neu: `src/Building/ValueObject/BuildingType.php` (`CONSTRUCTION_HUB` etc.)
- `src/Building/Service/BuildingDurationConfig.php` (Context-Aware)
- `src/Building/Service/BuildBuildingCommandService.php` + Upgrade-Service

## Fixtures Needed

- Nein
