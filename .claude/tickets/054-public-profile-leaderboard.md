# T-054: Public Player-Profile + Leaderboard

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No (Score evtl. denormalisiert later)
**Depends on:** T-043

## Description

Multiplayer-Feature. Öffentliche Profile (Spielername, Score, Allianz, Planeten-Anzahl) + Leaderboard nach Score-Kriterien (Wirtschaft, Militär, Forschung, Gesamt).

## AC

- [ ] `/p/{displayName}` Public-Profile-Route
- [ ] Score-Berechnung: Service mit getrennten Kategorien (econ, military, research)
- [ ] Score-Update: nach Tick aktualisieren oder on-demand berechnen?
- [ ] `/leaderboard?category=…` mit Pagination + Sortierung
- [ ] Privacy-Setting im User-Profil: opt-out aus Leaderboard?
- [ ] IT: Profile-Anzeige, Leaderboard-Sortierung

## Affected

- Neu: `src/Player/Controller/PublicProfileController.php`
- Neu: `src/Player/Controller/LeaderboardController.php`
- Neu: `src/Player/Service/ScoreCalculator.php`

## Open Questions

1. Single vs Multi → wenn Single, dieses Ticket entfällt oder reduziert sich auf Personal-Stats.
2. Score-Update on-tick oder on-demand? On-Tick = teuer aber UI-schnell. On-Demand = umgekehrt.
3. Score-Kategorien-Liste finalisieren.
