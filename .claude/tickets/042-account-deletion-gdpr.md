# T-042: Account-Löschung (GDPR / DSGVO)

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** Yes (deletedAt o.ä.)
**Depends on:** T-041, T-043

## Description

DSGVO-Pflicht. User muss Account löschen können + Datenexport-Pflicht. Soft-Delete für Game-Auswirkungen (z.B. zerstörte Schiffe behalten Owner-Hinweis), Hard-Delete personenbezogener Daten.

## AC

- [ ] "Account löschen"-Button in `/settings` (mit Passwort-Bestätigung)
- [ ] Sofort: User-PII (E-Mail, etc.) anonymisiert (z.B. `deleted+<id>@example.invalid`)
- [ ] Player-Entity: Bezeichnung anonymisiert (z.B. "[gelöschter Spieler]")
- [ ] Soft-Delete-Flag `deletedAt`
- [ ] Reaktivierung innerhalb Karenzzeit? (entscheiden)
- [ ] Daten-Export: `/settings/export` → JSON-Download mit allen User-Daten (User + Player + Planets + …)
- [ ] Login nach Deletion blockiert
- [ ] IT: Delete-Flow + Export-JSON-Format

## Affected

- `src/User/Entity/User.php` (deletedAt, anonymize-Methode)
- `src/Player/Model/Player.php` (anonymize Display-Name)
- Neu: `src/User/Controller/DeleteAccountController.php`, `DataExportController.php`
- Neu: `src/User/Service/AccountAnonymizer.php`

## Open Questions

1. Karenzzeit für Reaktivierung (z.B. 30 Tage)? Oder sofort hart?
2. Spielwelt: Player-Owned-Stuff (Planeten, Flotten) bei Delete an NPC fallen oder zerstört?
