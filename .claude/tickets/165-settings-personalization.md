# T-165 Settings / Personalization (UI-Theme, Default-Tactic, Galaxy-Filter)

**Type:** Feature
**Status:** Draft
**Effort:** S
**Depends on:** T-041 (User-Profile-Settings)
**Blocks:** —

## Beschreibung
Erweitert User-Profile-Settings (T-041) um Game-spezifische Personalization-Optionen.

## Acceptance Criteria
- [ ] UISettings-Entity (oder erweitert UserProfile, 1:1)
- [ ] Settings-Felder:
  - `uiTheme: enum LIGHT/DARK/IMPERIAL (default DARK)`
  - `defaultBattleTactic: enum FRONT_ASSAULT/FLANKING/HIT_AND_RUN/STANDOFF (default FLANKING)`
  - `galaxyMapFilters: JSON (welche POI-Types sichtbar)`
  - `notificationsTypes: JSON (T-161 type opt-in/out)`
  - `language: enum DE/EN`
- [ ] Settings-UI in Profile-Page
- [ ] Settings-Effekt: Default-Tactic-Wert pre-fills Battle-Tactic-Choice
- [ ] Galaxy-Map (T-160) liest Filter aus Settings
- [ ] Theme via CSS-Variables (Tailwind)

## Affected Tests
- tests/Settings/Service/SettingsPersistenceTest.php

## Fixtures Needed
No

## Notes
- Personalization niedrig-Risk: kein P2W-Hebel, pure QoL
- "Imperial-Theme" als 40k-Flavor-Bonus
