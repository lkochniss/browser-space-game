# T-047: In-Game-Benachrichtigungen

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** Yes (Notification-Tabelle)
**Depends on:** T-043, T-045

## Description

Events während Spieler offline (Schlacht, Forschung fertig, Sonde returned, Trümmerfeld entdeckt, …) → In-Game-Notification mit Lese-Status. Zentrale Anzeige im Dashboard (Glocken-Icon).

## AC

- [ ] `Notification` Entity (id, player, type, payload-JSON, createdAt, readAt)
- [ ] `NotificationDispatcher` Service mit `dispatch(Player, type, payload)`
- [ ] Hook-Points in Tick-Processoren (z.B. `BattleEncounterProcessor` triggert "battle_resolved")
- [ ] Dashboard: Glocken-Icon mit unread-Count, Liste aufklappbar
- [ ] `markAsRead` + `markAllAsRead` Endpoint
- [ ] Auto-Cleanup: gelesene Notifications älter als X Tage löschen (Cron-Job)

## Affected

- Neu: `src/Notification/` Domain
- Hooks in T-024 BattleResolver, T-025 Research, T-013 Probe-Return, etc.

## Open Questions

1. Notification-Types-Liste — finalisieren wenn Features stehen, oder upfront definieren?
2. E-Mail-Notifications (T-053) als opt-in pro Type?
