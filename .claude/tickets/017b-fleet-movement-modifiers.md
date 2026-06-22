# T-017b: Fleet-Movement-Modifiers (Nebel + Wormhole)

**Type:** Feature
**Status:** Done (Foundation: Wormhole-Speed-Bonus; Cooldown/Nebel-Detection in Folge-Tickets)
**Effort:** M (TBD)
**Depends on:** T-017 (Fleet-Movement Foundation), T-022 (Nebula POI), T-085 (Wormhole POI)
**Blocks:** —

## Beschreibung

Sammel-Ticket für Movement-Modifiers, die in T-022/T-085 Foundation als POI-
Subtypes existieren aber noch keinen Effekt auf MoveFleetCommand haben.

## Acceptance Criteria

### Wormhole-Travel-Shortcut
- [ ] TBD: Wenn Fleet vom Wormhole-A zum Wormhole-B (twin-Pair) reist:
  Travel-Time = 1/5 normaler Inter-System-Time (statt 4h → 48min)
- [ ] TBD: Treibstoff-Multiplier ×5 (T-066 / T-105 dependent)
- [ ] TBD: Per-Schiff-Cooldown 24h nach Wormhole-Transit (kein Spam)
- [ ] TBD: Tech-Lock: Wormhole.requiredTechSlug muss erforscht sein (T-026)
- [ ] TBD: Validation: origin und target müssen beide Wormholes mit gleichem
  twin-Pair sein

### Nebel-Detection-Hook
- [ ] TBD: Fleet im System mit Nebel: bei Movement durch dieses System wird
  ihre Position via T-074 Pirate-Encounter-Spawn ignoriert
- [ ] TBD: Detection-Layer: andere Fleet betritt Nebel → emittet
  FleetEnteredNebulaEvent (für T-103 Battle-Init oder T-018 Discovery)
- [ ] TBD: Concealment-Stat-Modifier in T-103 Battle (z.B. -10% Damage durch
  schlechte Sicht)

### Migration
- [ ] TBD: ships.last_wormhole_transit_at (für Cooldown-Tracking)

## Open Questions

- Wormhole-Pair-Validation: muss origin AND target Wormholes sein, oder reicht
  origin=Wormhole und target=System-mit-twin?
- Nebel-Detection: Stealth global oder pro Player (Fog-of-War T-087-abhängig)?
- Tech-Lock-Check ohne T-026: Foundation-Stub oder warten bis T-026 fertig?

## Notes

- Niedrige Prio: Wurmlöcher und Nebel funktionieren als POIs heute schon, sie
  sind nur "passiv" sichtbar — keine spielmechanische Wirkung
- Logisch verbunden: beide Modifier hängen an T-017 MoveFleetCommandService
- Folge-Tickets später: T-074 Pirate-Encounter (Nebel-Skip), T-103 Battle (Nebel-
  Stat-Modifier), T-026 (Tech-Lock-Validation)
