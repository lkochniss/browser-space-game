# TD-030: Deposit kann negativ werden + Level-Math fragwürdig

**Type:** TechDebt
**Status:** Open
**Severity:** Medium
**Effort:** S
**Affected Domain(s):** Tick, Resource, Building

## Beschreibung

`src/Tick/Processor/ResourceProductionProcessor.php:38-44`:
```php
$amount += $baseValue * ($building->getLevel() + 1) * $multiplier;
$deposit->setAmount($deposit->getAmount() - $amount);
```

Probleme:
1. **Deposit-Negativ:** Kein Clamp. Wenn `amount > deposit.amount` → Deposit wird negativ. Resource zählt trotzdem voll auf Player-Lager → Resource aus dem Nichts.
2. **Level off-by-one verdächtig:** `Building::createNewBuilding` setzt `level = 1`. Im Processor wird mit `(level + 1) = 2` multipliziert. Heißt: neues Lvl-1-Building produziert sofort 2× Base. Vermutlich unbeabsichtigt — entweder Level startet bei 0, oder Multiplikator sollte `level` (nicht `level+1`) sein.

## Risk if ignored

Wirtschafts-Bug. Cheating-Vektor (unendliche Resources über erschöpfte Deposits). Falsche Balance bei Building-Levels.

## AC

- [ ] Extraktion clamped: `$actual = min($desiredAmount, $deposit->getAmount())`; nur `$actual` an Resource gutschreiben
- [ ] Entscheidung Level-Start: bei 0 oder bei 1? Build-Counter konsistent korrigieren oder Multiplikator anpassen
- [ ] Kommentar im Code falls Designentscheidung (z.B. "level 1 = 1x base")
- [ ] Bei leerem Deposit kein Krash, einfach 0 gewinnen
- [ ] (Optional) DepositEmptyEvent dispatchen für T-020 (POI verschwindet)

## Refactor Strategy

Pure Logik-Fix + 1 Integration-Test (sobald T-031 PHPUnit existiert).
