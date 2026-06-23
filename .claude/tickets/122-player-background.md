# T-122 Player-Background (5 Backgrounds, Onboarding-Choice)

**Type:** Feature
**Epic:** Player Progression
**Domain:** Player
**Blocked By:** T-073
**Status:** Done (Foundation; Effect-Resolver + Onboarding-UI in T-122b split)
**Effort:** M
**Depends on:** T-073 (Faction, Done)
**Blocks:** T-122b

## Beschreibung
Bei Onboarding wählt Spieler 1 von 5 Backgrounds. Klein-Boni/Mali +5%/-2% für Flavor-Identity. **PERMANENT — keine Re-Spec.**

Backgrounds (40k-flavored, Imperialer Mensch):
- **Imperialer Adel**: +5% Reputation-Speed, -2% Mining-Output
- **Aufsteiger** (Common-Born Industrial): +5% Mining-Output, -2% Reputation-Speed
- **Tech-Adept** (Mechanicum-affiliated): +5% RP-Output, -2% Pop-Wachstum
- **Veteran-Pilot**: +5% Schiff-Speed/Combat-Crit, -2% Pop-Wachstum
- **Wanderer** (Frontier-Born): +5% Sonden-Range/Discovery-Speed, -2% Trade-Income

## Acceptance Criteria

- [x] `PlayerBackground` Enum (5 Werte) mit `getDisplayName()` + `getDescription()`
- [x] `Player.background: ?PlayerBackground` Field (nullable bis Wahl)
- [x] `Player::setBackground()` mit Re-Spec-Block (`BackgroundAlreadySetException`)
- [x] `SetPlayerBackgroundCommand` + Handler + Service mit `PlayerNotFoundException`
- [x] Migration `Version20260622000005` (players.background nullable)
- [x] Demo-CLI Action "Set Background" (PERMANENT-Confirm-Dialog)
- [x] Tests: PlayerBackgroundTest (Unit) + SetPlayerBackgroundCommandServiceTest (IT, 4 Tests)
- [x] Doc `player.md` Background-Sektion

## Out of Scope (in T-122b verschoben)

- **Effect-Resolver** für die 7 Hook-Stellen (Mining/Reputation/RP/Pop-Growth/
  Ship-Speed/Probe-Range/Trade-Income)
- **Stack-Reihenfolge** mit T-098 Specialist-Tracks (Q1 in T-122b)
- **Onboarding-UI** (T-046 Open) — Demo-CLI deckt das interim ab
- **Public-Profile** (T-054 Open)
- **Cosmetic-Anteil** (Welcome-Message, Banner-Default) — T-122c Folge

## Notes
- Klein-Boni absichtlich (5%/-2%): kein Min-Maxing, eher Identity-Bonus
- Differenziert sich von Specialist-Track (T-098, +30%/-10%): Background = Flavor, Track = Mechanik
- Beide Choices kombinierbar (z.B. Adel + Diplomacy-Track passend, aber nicht erzwungen)
