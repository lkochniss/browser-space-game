# T-098 Specialist-Tracks (Forced Specialization)

**Type:** Feature
**Epic:** Player Progression
**Domain:** Player
**Blocked By:** T-025, T-046
**Status:** Draft
**Effort:** L
**Depends on:** T-025 (Forschungs-Framework), T-046 (Onboarding)
**Blocks:** T-117 (Allianz-Forschung)

## Beschreibung
Spieler wählt 1 von 5 Specialist-Tracks bei Onboarding (oder erstem Endgame-Schwellenwert). Wahl ist **PERMANENT — keine Re-Spec**.

Tracks:
- INDUSTRY: +30% Mining/Refining-Output, -10% Combat-Damage
- MILITARY: +30% Combat-Damage + Schild-HP, -10% Mining-Output
- RESEARCH: +30% RP-Output + 1 Tier-Branch komplett unlockable, -10% Bauzeit-Speed
- DIPLOMACY: +30% Reputation-Gain + Trade-Steuer-Reduktion, -10% Defense-Stats
- EXPLORATION: +30% Sonden-Range + Schiff-Speed, -10% Production-Output

## Acceptance Criteria
- [ ] SpecialistTrack-Enum (5 Werte)
- [ ] Player-Entity: `specialistTrack: ?SpecialistTrack` (nullable bis Wahl)
- [ ] Specialist-Track-Selection als Onboarding-Schritt (T-046 Erweiterung)
- [ ] Track-Effekt-Resolver: applies Multipliers in Tick-Processors + Battle-Engine
- [ ] Branch-Lock: Specialist-Track unlocked 1 Branch komplett (alle 5 Tier), andere nur Tier 1-3
  - INDUSTRY → Mining/Industrie-Branch full
  - MILITARY → Schiffbau-Branch full
  - RESEARCH → Wissenschaft-Branch full (welcher? Kybernetik vermutlich)
  - DIPLOMACY → Diplomatie-Branch full
  - EXPLORATION → Antrieb-Branch full
- [ ] **PERMANENT**: kein API/UI für Re-Spec
- [ ] Visible in Public-Profile (T-054)

## Affected Tests
- tests/Player/Service/SpecialistTrackEffectTest.php (Multipliers wirken)
- tests/Research/Service/BranchLockTest.php (Tier-4-5-Gate)

## Fixtures Needed
Yes — Player pro Track

## Notes
- "Forced Specialization" → Spieler muss Allianz-Diversität nutzen für komplette Tech-Coverage
- Anti-Steamroller: Permanent-Choice macht Solo-Mastery aller Felder unmöglich
- Konsistenz: T-117 Allianz-Forschung ergänzt durch Cross-Track-Donations
