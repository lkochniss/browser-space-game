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

## Decisions (geklärt 2026-06-19)

1. **Stacking:** multiplikativ (0.9 × 0.8 × 0.7) — konsistent zu T-151 SoftCap-Pattern; natural diminishing returns; Floor-Cap bleibt sauber.
2. **Speed-Tech-Branch-Placement:** offen, im Rahmen von T-025 zu klären.
3. **Retroaktiv:** Nein — nur neue Bauten. `finishedAt` ist immutable nach Start; konsistent zu T-062 isReady-Logic; vermeidet Race-Conditions.
4. **Upgrades = Initial-Bauten:** Multiplier wirkt auf beide gleich.

## Affected

- Neu: `src/Building/Service/ConstructionSpeedMultiplierService.php`
- Neu: `src/Building/ValueObject/BuildingType.php` (`CONSTRUCTION_HUB` etc.)
- `src/Building/Service/BuildingDurationConfig.php` (Context-Aware)
- `src/Building/Service/BuildBuildingCommandService.php` + Upgrade-Service

## Fixtures Needed

- Nein
