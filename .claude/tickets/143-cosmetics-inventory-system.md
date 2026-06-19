# T-143 Cosmetics-Inventory-System

**Type:** Feature
**Status:** Draft
**Effort:** M (TBD)
**Depends on:** T-141 (Achievements), T-080 (Loot-Drops)
**Blocks:** —

## Beschreibung

Cluster-F-Folge: Cosmetics aus Achievements (T-141), Loot-Drops (T-080), Crusade-Wins (T-121), Login-Streaks (T-142) brauchen einheitliches Inventory-System.

Cosmetic-Slots:
- **Title**: vor/hinter Player-Name (z.B. "Crusader of Cycle 3", "Lord of the Forge")
- **Banner**: Allianz-Banner / Player-Banner (Profil-Pic-Equivalent)
- **Frame**: Profil-Frame (Bronze/Silver/Gold/Platinum-Frame aus Achievements)
- **Schiff-Skin**: Cosmetic-Schiff-Lackierung
- **Building-Skin**: Cosmetic-Hub-Variation

## Acceptance Criteria

- [ ] TBD: PlayerCosmeticInventory-Entity
- [ ] TBD: CosmeticItem-Catalog (Foundation 20-50 Items)
- [ ] TBD: Equip-Slots (5 fixed: Title/Banner/Frame/ShipSkin/BuildingSkin)
- [ ] TBD: Unlock-Trigger: Achievement-System ruft CosmeticUnlockService
- [ ] TBD: UI: Cosmetic-Equip-Page in Profile-Settings
- [ ] TBD: Display in Public-Profile (T-054), Chat (T-053), Leaderboard

## Open Questions

- Cosmetics nur eigen-equipbar oder auch teilbar (z.B. Banner für Allianz)?
- Schiff-Skins pro Klasse oder generisch?
- Permanent-Unlock oder ephemerer (z.B. Crusade-Title nur 6 Wochen sichtbar)?

## Notes

- **Cosmetics-only-Decision** (Cluster F): keine Stat-Boni, pure Status-Symbol
- Cosmetics als Long-Term-Reward-Anker für Endgame-Spieler
