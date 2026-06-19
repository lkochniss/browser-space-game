# T-168: Demo-CLI Auto-Switch in demo-Env

**Type:** Bug
**Status:** Done
**Severity:** Medium (verwirrt jeden, der den Demo zum ersten Mal startet)
**Effort:** S (~30min)

## Symptom

Im docker-compose php-Container:

```
$ php bin/console app:demo:run
[critical] Error thrown while running command "app:demo:run".
Message: "An exception occurred in the driver: SQLSTATE[HY000] [2002] Connection refused"
```

Schema-Setup beginnt, aber die Default-DATABASE_URL (`.env`) zeigt auf MySQL
auf 127.0.0.1, der im PHP-Container nicht erreichbar ist (sollte `mysql:3306`
oder ähnlich sein, je nach Compose-Setup).

## Root Cause

`app:demo:run` ist auf **demo-Umgebung** designed (eigene SQLite-File `var/demo.db`,
isoliert via `.env.demo`). Wird der Command ohne `APP_ENV=demo` / `--env=demo`
gestartet, nutzt Symfony die `dev`-Default-DATABASE_URL → MySQL → fail.

Es gibt keinen Pre-Flight-Check, der dem User sagt "du brauchst `--env=demo`".

## Fix

InteractiveDemoCommand prüft am Anfang von `execute()` ob `kernel.environment`
== `demo`. Falls nicht: **Auto-Re-Exec via Symfony Process** in Sub-Prozess
mit `APP_ENV=demo` + `--env=demo`. setTty=true reicht stdin/stdout durch →
Choice-Loop bleibt interaktiv. Fallback ohne TTY für non-tty / --no-interaction.

User braucht jetzt nur:
```bash
php bin/console app:demo:run            # läuft, auto-switch zu demo
php bin/console app:demo:run --reset    # läuft, auto-switch + reset
APP_ENV=demo php bin/console app:demo:run --env=demo  # explizit, kein switch
```

## Acceptance Criteria

- [x] `InteractiveDemoCommand` injiziert `kernel.environment` + `kernel.project_dir` via `#[Autowire]`
- [x] Auto-Re-Exec: wenn != 'demo' → Symfony\Process startet Sub-Prozess mit korrekten Flags
- [x] TTY-Pass-through für interaktiven Choice-Loop
- [x] Fallback (Output-Stream-Forwarding) für non-TTY-Umgebungen
- [x] User-sichtbarer NOTE: "Auto-switch X → demo env"
- [x] Suite grün (515/515)
- [x] Smoke-Test: Command startet aus test env → Re-Exec → Setup + Status erscheinen

## Files

**Geändert:**
- `src/Demo/Command/InteractiveDemoCommand.php`
