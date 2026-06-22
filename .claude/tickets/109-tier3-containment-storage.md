# T-109 Tier-3 Containment-Storage (Antimatter / AI-Core / Bound-Resources)

**Type:** Feature
**Status:** Blocked (by T-177 — Storage-Buildings-Konzept wird refactored)
**Effort:** S (TBD)
**Depends on:** T-115 (Tier-3-Resources), T-092 (Rare-Resources), T-177 (Generic-Storage-Refactor — superseded T-061)
**Blocks:** —

## Beschreibung

Storage-Buildings für Tier-3 / Rare-Resources. Notwendig wegen Hazard-Charakteristik (Antimatter-Containment) + extrem niedrigen Storage-Caps für Endgame-Resources.

Neue Buildings:
- ANTIMATTER_CONTAINMENT: Storage für ANTIMATTER (T-115); Power-Verbrauch hoch (50/lvl) auch wenn nicht voll; Sicherheits-Risiko bei Power-Loss?
- AI_CORE_VAULT: Storage für AI_CORE; klein, aber sicher
- ADAMANTIUM_DEPOT: Storage für ADAMANTIUM, PLASTEEL (Schwermetalle)
- VOID_CONTAINER: Storage für VOID_ESSENCE, DARK_MATTER_FRAGMENT (T-092 Rare); minimaler Cap
- ARTIFACT_VAULT: für XENOS_ARTIFACT, ANCIENT_DATA_CORE; Forschungs-Bonus passiv

## Acceptance Criteria

- [ ] TBD: Neue BuildingType-Werte mit StorageContribution
- [ ] TBD: Antimatter-Containment-Hazard: bei Power-Mangel (T-065) → Containment-Failure-Event (Pop-Loss)
- [ ] TBD: Artifact-Vault: passiver Forschungs-Speed-Bonus pro gelagertem Artifact

## Open Questions

- Hazard-Mechanik bei Power-Loss zu hart? (Game-Over-Risk)
- Storage-Cap-Multiplier: VOID_CONTAINER nur 5 VOID_ESSENCE/lvl?
- Artifact-Forschungs-Bonus: alle Branches gleich oder per-Branch?

## Notes

- Tier-3-Storage als Bottleneck → Spieler muss vor Tier-3-Production-Build erst Containment haben
- Klein aber wichtig — kein Megaeffort
