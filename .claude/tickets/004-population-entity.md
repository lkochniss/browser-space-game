# T-004: Population on Planet

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No

## Description

`docs/Bevölkerung.md`: Population ist Kernzahl. Drei Werte: total, free, assigned. Cap pro Planet. Zugewiesene Pop wird durch Buildings/Ships gebunden, freie ist verfügbar. Aktuell keine Pop im Code.

## AC

- [ ] `Population` Value Object oder Entity (`total`, `assigned`, `cap`) — `free = total - assigned`
- [ ] Planet enthält `Population`
- [ ] Methoden: `assign(int)`, `release(int)`, `grow(int)`, `kill(int)`
- [ ] `cap` initial gemäß Planet-Default (z.B. 100); Hub erhöht (T-006)
- [ ] Start-Pop nach `claimPlanet` definiert (z.B. 50)

## Affected

- `src/Planet/Model/Planet.php`
- Neu: `src/Population/Model/Population.php` (oder unter `Planet/Model/`)

## Open Questions

1. Eigene Domain `Population/` oder Sub von `Planet/`? Empfehlung: Sub, da Pop ohne Planet keinen Sinn hat.
2. Negative-Wachstum Logik (Hungersterben) jetzt oder in T-005?
