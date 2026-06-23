# T-169: Demo "Reset Demo"-Action lässt Player ohne Buff/Galaxy + Loop verirrt sich

**Type:** Bug
**Epic:** Tech-Debt & Cleanup
**Domain:** Demo
**Blocked By:** None
**Status:** Done
**Severity:** High (Reset-Funktion komplett kaputt)
**Effort:** XS (~20min)

## Symptom

User wählt "Reset Demo" im Menü, bestätigt Confirm. Danach:
- Status zeigt: `No planets — reset demo.`
- Resource-Buff fehlt (kein IRON_ORE etc.)
- Galaxy-Garantie (Wormhole-Pair, DebrisField) fehlt
- Demo bricht effektiv ab

## Root Cause

`resetSession` macht:
1. SchemaTool drop + create
2. FactionSeed
3. ClaimStartPlanetCommand mit **neuer** PlayerId

Aber:
- `applyDemoBuff(...)` wird **nicht** aufgerufen
- `ensureDemoGalaxyContent()` wird **nicht** aufgerufen
- Main-Loop ruft danach `$player = $this->playerRepository->find($player->getId())` mit der **alten** PlayerId — die nach drop+create nicht mehr existiert. Lookup scheitert, Loop signalisiert "no planets" + bricht ab

Während `setupSession(reset: true)` denselben Code-Pfad korrekt mit Buff + Garantie ausführt, dupliziert `resetSession` die Logik unvollständig.

## Fix

1. Bootstrap-Logik in privater Methode `bootstrapFreshPlayer(): ?Player` zentralisieren
2. Sowohl `setupSession`-Reset-Pfad als auch `resetSession` rufen sie auf
3. `resetSession` propagiert neuen Player über privates Feld `$pendingPlayerSwap`
4. Main-Loop prüft das Feld nach jeder Iteration und schwenkt $player um, statt blind by-old-ID zu suchen

## Acceptance Criteria

- [ ] `bootstrapFreshPlayer()` Helper existiert + wird von beiden Pfaden genutzt
- [ ] Reset-Action liefert Player mit Hub L1 + 3000 IRON_ORE-Buff + DebrisField/Wormhole
- [ ] Main-Loop bricht nach Reset NICHT ab; neue Status-Anzeige zeigt frische Welt
- [ ] Smoke-Test: Reset → Status zeigt korrekten Bestand
- [ ] Suite grün

## Files

**Geändert:**
- `src/Demo/Command/InteractiveDemoCommand.php` (`bootstrapFreshPlayer`, `resetSession`, Main-Loop)
