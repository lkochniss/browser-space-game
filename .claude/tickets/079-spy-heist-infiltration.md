# T-079 Spy-Heist + Infiltration (Erweiterung zu T-131)

**Type:** Feature
**Status:** Draft
**Effort:** L (TBD)
**Depends on:** T-131 (Spy-Foundation), T-080 (Loot)
**Blocks:** —

## Beschreibung

Erweiterung der Spy-Mission-Types über Recon + Sabotage hinaus. Bleibt **PvE-only** (kein Spielerspion).

Neue Mission-Types:
- **Heist**: Spy infiltriert NPC-Outpost und stiehlt Loot (Tier-3-Resources, Blueprints) — höhere Erfolgsanforderung als Sabotage
- **Infiltration**: Spy taucht in NPC-Faction unter, sammelt Long-Term-Intel über mehrere Wochen, generiert kontinuierlich Bonus-Information (Outpost-HP-Updates, Loot-Estimates, Spawn-Timings)

## Acceptance Criteria

- [ ] TBD: HeistMission-Type mit Loot-Steal-Mechanik
- [ ] TBD: InfiltrationMission als Long-Running (Weekly-Intel-Drops)
- [ ] TBD: Failure-Mode: Heist-Failure = Spy-Permadeath + Reputation-Crash zur Target-Faction
- [ ] TBD: Anti-Player-Lock konsequent durchgezogen (Type-Validation)

## Open Questions

- Heist-Loot-Skalierung: Anteil des Outpost-Inventars stehlbar?
- Infiltration-Cooldown: ein Spy gleichzeitig in einer Faction, oder mehrere parallel?
- Infiltration-Discovery: zufällige Auf-Deckung mit Counter-Espionage-Mechanik?

## Notes

- Höhere Risiko + höhere Reward als T-131 Foundation
- Renegade-Rep-Path über Heists gegen Imperium-Outposts (sobald Imperium-Outposts existieren)
- T-113 Black-Market-Access via Renegade-Rep wird durch diese Missions schneller erreichbar
