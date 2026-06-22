# T-026b: Wormhole-spezifischer Tech-Lock

**Type:** Feature
**Status:** Superseded by T-017b
**Effort:** S (~1h)
**Depends on:** T-026 (Antrieb-Tree), T-085 (Wormhole-POI)
**Blocks:** —

## Beschreibung

`Wormhole.requiredTechSlug` Field existiert + ist auf `'ftl_warp'` gesetzt
(durch ClaimStartPlanet, WorldFixture, Demo). Aber: wird **nirgends validiert**.
T-026 hat globalen Inter-System-Travel-Lock (`ftl_hyperdrive` L1+), aber keine
Wormhole-spezifische Prüfung.

## Superseded by T-017b (2026-06-22)

T-017b (Fleet-Movement-Modifiers) implementiert die `wormhole.requiredTechSlug`-
Prüfung — aber mit **softerer Semantik** als das ursprüngliche T-026b-Spec:

- T-026b-Spec war: ohne Tech → **Reject** (`WormholeTechRequiredException`)
- T-017b-Implementation ist: ohne Tech → **Fallback** auf normale Inter-System-
  Reise; kein Hard-Block, nur kein Speed-Bonus

User-Decision war explizit "Fallback statt Hard-Block". Damit ist T-026b's
Mechanik vollständig in T-017b absorbiert — eigene Implementation wäre Reversal
der User-Decision. **Closed**.

## Acceptance Criteria

- [ ] MoveFleetCommandService: wenn Origin/Target-Pair via Wormhole-Pair
      verbunden ist → prüfen ob Player das `wormhole.requiredTechSlug`
      Forschungs-Level hat
- [ ] Neue Exception `WormholeTechRequiredException`
- [ ] Foundation: Routing-Logik kann pragmatisch sein — z.B. wenn ein
      Wormhole im Origin-System existiert das mit Target-System verbunden
      ist, wird die Wormhole-Tech-Anforderung zusätzlich geprüft
- [ ] Tests: Travel-mit-FTL-aber-ohne-Warp wird abgelehnt wenn Pair-Match;
      Travel-zwischen-non-Wormhole-Systems funktioniert wie heute
- [ ] Doc: poi.md Wormhole-Sektion + research.md ftl_warp-Hook

## Notes

- Foundation kann ohne Routing-Engine auskommen: wenn beide Systems via
  Wormhole-Pair connected sind → Wormhole-Tech-Check; sonst nur ftl_hyperdrive
- Späteres Routing (kürzester Pfad mit Wormhole-Sprüngen) ist eigenes Ticket
