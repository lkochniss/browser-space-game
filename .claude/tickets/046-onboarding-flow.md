# T-046: Onboarding-Flow für neuen Spieler

**Type:** Feature
**Epic:** Web Layer & Auth
**Domain:** User
**Blocked By:** T-037, T-043
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-037, T-043

## Description

Erstmaliger Login (User hat noch keinen Player) → Onboarding: Spielername wählen, Start-Planet zugewiesen, kurze Tutorial-Tour über Dashboard.

## AC

- [ ] Listener / Middleware: Login ohne Player → Redirect `/onboarding`
- [ ] Step 1: Spielername (eindeutig, validiert)
- [ ] Step 2: Start-Planet wird automatisch erzeugt + claimed (nutzt existing `ClaimStartPlanetCommand`)
- [ ] Step 3: kurze Tutorial-Tour (Stimulus-Tour-Library oder simple Modal-Sequenz)
- [ ] Nach Onboarding → Redirect `/game`
- [ ] IT: Onboarding-Flow E2E

## Affected

- Neu: `src/User/Controller/OnboardingController.php`
- Neu: `src/User/EventListener/OnboardingRedirectListener.php`
- Neu: `templates/onboarding/*.html.twig`

## Open Questions

1. Tutorial überspringbar?
2. Spielername-Änderung später möglich (in Settings)? Cooldown?
