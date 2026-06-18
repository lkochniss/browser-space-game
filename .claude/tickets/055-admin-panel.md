# T-055: Admin-Panel

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-037

## Description

Basis-Admin für User-Verwaltung + Game-State-Inspector. Geschützt mit `ROLE_ADMIN`.

## AC

- [ ] `composer require easycorp/easyadmin-bundle` (oder eigenes Lite-Panel)
- [ ] CRUD für `User` (suchen, sperren, Roles ändern, manuell verifizieren)
- [ ] CRUD/Inspector für `Player`, `Planet`, `SolarSystem` (read-only sinnvoll, manuelles Edit für Support)
- [ ] Tick-Run-Trigger im Admin (manuell)
- [ ] Audit-Log für Admin-Aktionen (wer, was, wann)
- [ ] Geschützt mit `ROLE_ADMIN` Voter
- [ ] IT: nicht-Admin → 403, Admin → 200

## Affected

- Neu: `src/Admin/Controller/`
- evtl. `composer.json`
- `config/packages/security.yaml` (Access-Control)

## Open Questions

1. EasyAdmin (schnell, Standard) vs eigenes (Tailwind-Style passend)? Vorschlag: EasyAdmin für MVP.
2. Audit-Log als eigenes Ticket oder hier inklusiv? Hier OK.
