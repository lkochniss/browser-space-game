# T-122 Player-Background (5 Backgrounds, Onboarding-Choice)

**Type:** Feature
**Status:** Draft
**Effort:** M
**Depends on:** T-046 (Onboarding)
**Blocks:** —

## Beschreibung
Bei Onboarding wählt Spieler 1 von 5 Backgrounds. Klein-Boni/Mali +5%/-2% für Flavor-Identity. **PERMANENT — keine Re-Spec.**

Backgrounds (40k-flavored, Imperialer Mensch):
- **Imperialer Adel**: +5% Reputation-Speed, -2% Mining-Output
- **Aufsteiger** (Common-Born Industrial): +5% Mining-Output, -2% Reputation-Speed
- **Tech-Adept** (Mechanicum-affiliated): +5% RP-Output, -2% Pop-Wachstum
- **Veteran-Pilot**: +5% Schiff-Speed/Combat-Crit, -2% Pop-Wachstum
- **Wanderer** (Frontier-Born): +5% Sonden-Range/Discovery-Speed, -2% Trade-Income

## Acceptance Criteria
- [ ] PlayerBackground-Enum (5 Werte)
- [ ] Player-Entity: `background: ?PlayerBackground` (nullable bis Onboarding)
- [ ] Onboarding-UI (T-046): Background-Auswahl als Schritt vor Start-Planet-Claim
- [ ] Effekt-Resolver: applies Multipliers überall
- [ ] **PERMANENT** — kein Re-Spec-Endpoint
- [ ] Visible in Public-Profile (T-054)
- [ ] Cosmetic-Anteil: Background-spezifische Banner-Default-Auswahl, Flavor-Texte in Welcome-Message

## Affected Tests
- tests/Player/Service/PlayerBackgroundEffectTest.php

## Fixtures Needed
Yes — Players pro Background

## Notes
- Klein-Boni absichtlich (5%/-2%): kein Min-Maxing, eher Identity-Bonus
- Differenziert sich von Specialist-Track (T-098, +30%/-10%): Background = Flavor, Track = Mechanik
- Beide Choices kombinierbar (z.B. Adel + Diplomacy-Track passend, aber nicht erzwungen)
