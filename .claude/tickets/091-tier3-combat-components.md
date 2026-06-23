# T-091 Tier-3-Combat-Komponenten (Targeting / Reactive-Armor / Plasma-Coil / ECM)

**Type:** Feature
**Epic:** Resources Tier-2/3
**Domain:** Resource
**Blocked By:** T-067, T-115, T-102
**Status:** Draft
**Effort:** L (TBD)
**Depends on:** T-067 (Tier-2), T-115 (Tier-3-Resources), T-102 (Schiff-Klassen)
**Blocks:** Mark-II/Mark-III Schiff-Builds

## Beschreibung

Spezialisierte Combat-Komponenten für höhere Schiff-Marks. Differenzieren Mk I/II/III über reine HP-/Damage-Skalierung hinaus.

Neue Tier-3 Erzeugnisse:
- TARGETING_COMPUTER (aus AI-Core + Chip + Adamantium) — +Crit-Chance, +Hit-Rate
- REACTIVE_ARMOR (aus Plasteel + Hull-Plate + Tritium) — Damage-Reduction
- PLASMA_COIL (aus Plasma-Cell + Composite + Adamantium) — Beam-Weapon-Tier
- ECM_SUITE (aus Chip + AI-Core + Tritium) — Stealth/Counter-Stealth
- WARP_DRIVE_CORE (aus Plasma-Cell + Adamantium + AI-Core) — FTL-Antrieb-Komponente

Mark-Tier-Mapping (Vorschlag):
- Mk I: nur Hull-Plate + Shield-Module + Steel
- Mk II: zusätzlich Targeting-Computer + Reactive-Armor
- Mk III: zusätzlich Plasma-Coil + ECM-Suite

## Acceptance Criteria

- [ ] TBD: Neue ResourceTypes inkl. Recipes
- [ ] TBD: Manufacturing-Buildings: TARGETING_LAB, ARMOR_FORGE, PLASMA_FORGE, ECM_LAB, WARP_FOUNDRY
- [ ] TBD: T-102 Mark-Tier-Cost-Recipes integrieren neue Komponenten
- [ ] TBD: Battle-Engine (T-103) liest Equipped-Components → Stat-Multiplier (Crit, DmgReduction, Stealth)

## Open Questions

- Component-Slot-System pro Schiff (Modular) oder fixe Mark-Configs?
- Stealth-Mechanik: Detection-vs-ECM-Wert?
- Warp-Drive-Core auch FTL-Antrieb-Voraussetzung (verbindet zu T-026)?

## Notes

- Forced-Specialization-Feedback-Loop: Spieler ohne Industrie-Track muss Components zukaufen → Wirtschafts-Hebel
- Anti-Steamroller: Mk-III-Komponenten so teuer dass max 1-2 Mk-III-Schiffe pro Spieler realistisch
