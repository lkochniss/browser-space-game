# Tickets

| File | Type | Epic | Domain | Status | Blocked By | Summary |
|------|------|------|--------|--------|------------|---------|
| 001-renewable-resources.md | Feature | Foundation: Resources | Resource | Done | None | WATER/FOOD/OXYGEN als ResourceType; Start-Amount 100; Base-Werte gesetzt |
| 002-finite-resources-extend.md | Feature | Foundation: Resources | Resource | Done | None | 6 Erze + 6 Mines + Base-Werte gestaffelt; canProduce-Bug gefixt |
| 003-erzeugnis-eisenbarren.md | Feature | Foundation: Resources | Resource | Done | T-002 | ResourceCategory + IRON_BAR + IRON_SMELTER + Refinement-Tick (2:1:1) (T-167 Status-Sync) |
| 004-population-entity.md | Feature | Foundation: Population | Planet | Done | None | Embeddable Population (total/assigned/cap) auf Planet + Migration |
| 005-population-consumption.md | Feature | Foundation: Population | Planet | Done | T-001, T-004 | Pop-Verbrauch W/F + Logistic Growth + Mangel-Kill (free first) |
| 006-hub-building.md | Feature | Foundation: Buildings | Building | Done | T-004 | HUB BuildingType + Cap +50/Level + Auto-Recalc in addBuilding |
| 007-sonnensystem-domain.md | Feature | Foundation: Galaxy | SolarSystem | Done | None | SolarSystem-Entity + Planet→System + 5-System-Galaxy beim Claim |
| 008-planet-types-sizes.md | Feature | Foundation: Planet Types | Planet | Done | None | 5 Sizes + 7 Types + Consumption-Multi + Type-basierte Deposits |
| 009-building-cost-construction.md | Feature | Foundation: Buildings | Building | Done | T-004 | BuildingCost + BuildBuildingCommand + Pop-Bindung; Echtzeit-Stub |
| 010-building-upgrade.md | Feature | Foundation: Buildings | Building | Done | T-009 | UpgradeBuildingCommand + 2^level Skalierung + Cap-Recalc |
| 011-raumwerft.md | Feature | Ships & Fleet | Building | Done | T-009 | SHIPYARD BuildingType + Cost/Duration + Planet::getShipyardLevel/hasShipyard |
| 012-raumschiff-base.md | Feature | Ships & Fleet | Ship | Done | T-001, T-011 | Ship-Foundation + BuildShipCommand + ShipSupplyProcessor; ShipType-Stub GENERIC |
| 013-sonde-types.md | Feature | Exploration & Probes | Probe | Done | T-012 | Probe-Domain (SYSTEM/ORBITAL/DEEP_SCAN) + PROBE_LAB Building + BuildProbeCommand |
| 014-kolonisationsschiff.md | Feature | Ships & Fleet | Ship | Done | T-007, T-012 | COLONY_SHIP + ColonizePlanetCommand mit Pop-Transfer + ShipCostConfig-Refactor |
| 015-transportschiff.md | Feature | Ships & Fleet | Ship | Done | T-012 | 3 Transport-Klassen + CargoManifest Embeddable + Load/Unload/DockCommands |
| 015b-station-cargo-transfer.md | Feature | Ships & Fleet | Ship | Done | T-015, T-023 | Ship.station-Field + LoadCargo/UnloadCargo branch für Station-Storage; Pop-Transfer skip |
| 015c-station-pop-transfer.md | Feature | Ships & Fleet | Ship | Done | T-015b, T-023b | Pop-Transfer Ship ↔ Station via station.populationOnStation; Cap-Check defer T-023b |
| 016-bergungsschiff.md | Feature | Ships & Fleet | Ship | Done | T-012, T-020 | ShipType::SALVAGE + Echtzeit-Salvage (50 Units/min) für AsteroidField + Field-Cleanup |
| 017-flotte-movement.md | Feature | Ships & Fleet | Fleet | Done | T-007, T-012 | Persistent-Fleet + Wallclock-Movement (Slowest-Ship-Speed) + FleetArrivalService; Magic-Dock-Cleanup |
| 017b-fleet-movement-modifiers.md | Feature | Ships & Fleet | Fleet | Done | T-017, T-022, T-085 | Wormhole-Travel ×0.2 wenn Pair zw. Systemen + Player hat Wormhole-Tech; Fallback ohne Tech = normal |
| 018-teleskop-discovery.md | Feature | Exploration & Probes | Probe | Done | T-007, T-019 | TELESCOPE-Building + PlayerSystemDiscovery + Tick-Reveal; Demo-Galaxy-Overview filtert auf entdeckte |
| 019-poi-system.md | Feature | POI System | POI | Done | T-007 | POI-Foundation (STI) + 7 PoiTypes + SolarSystem.pois |
| 020-asteroidenfeld.md | Feature | POI System | POI | Done | T-019 | AsteroidField POI-Subtype (STI) + Galaxy-Spawn 0-2 pro System mit FINITE-Erzen |
| 021-truemmerfeld-recycling.md | Feature | POI System | POI | Done | T-019, T-103 | DebrisField POI + DEBRIS-ResourceTypes + RECYCLING_PLANT + RecyclingProcessor; Spawn via killShip + Fixture |
| 022-nebel-poi.md | Feature | POI System | POI | Done | T-019 | Nebula POI-Subtype (STI) + concealmentLevel + 30%-Galaxy-Spawn |
| 023-raumstation.md | Feature | POI System | POI | Done | T-007, T-011, T-019 | SpaceStation POI (max 1/System, Shipyard-L3-Gate, Storage 100k); Maintenance/Übernahme = T-023b |
| 023b-station-maintenance-takeover.md | Feature | POI System | POI | Draft | T-023, T-005 | Station-Maintenance-Tick + Pop-Mortality + ABANDONED-State + ClaimAbandonedStationCommand |
| 024-raumschlacht.md | Feature | Combat & Battle | Ship | Superseded by T-103 | T-017, T-021, T-073 | Abgelöst durch T-103 (T-167 Cleanup) |
| 025-forschung-framework.md | Feature | Research & Tech-Tree | Research | Done | None | Wallclock-Forschung Foundation: Node + Active/PlayerResearch + RESEARCH_LAB-Building + Demo-Action + Stub-Nodes |
| 025b-multi-lab-research-boost.md | Feature | Research & Tech-Tree | Research | Done | T-025 | Auto-Aggregator (geometric decay 0.5); wird durch T-025c-Opt-In-Modell ersetzt |
| 025c-multi-lab-opt-in-with-cost.md | Feature | Research & Tech-Tree | Research | Done | T-025, T-025b | Opt-In Multi-Lab beim StartResearch: Geometric-Decay-Bonus + Flat/Quadratic-Cost-Penalty; JSON-Persistence frozen-at-start |
| 026-antriebstechnologie-tree.md | Feature | Research & Tech-Tree | Research | Done | T-025 | 7 Antriebs-Nodes + Inter-System-Travel-Lock (ftl_hyperdrive); PropulsionType/Fuel via Folge |
| 026b-wormhole-tech-validation.md | Feature | Research & Tech-Tree | Research | Superseded by T-017b | T-026, T-085 | Durch T-017b absorbiert (Fallback-Semantik statt Hard-Block, User-Decision) |
| 026c-propulsion-type-field.md | Feature | Ships & Fleet | Ship | Done | T-026 | Ship.propulsion (7 types) + research-gate beim Build + Speed-Multiplier-Stack mit ShipType |
| 027-planetologie-research.md | Feature | Research & Tech-Tree | Research | Open | T-013, T-025 | Planetologie-Forschung (Sondendetails + Terraform-Gate) |
| 028-techdebt-wrong-namespaces.md | TechDebt | Tech-Debt & Cleanup | Planet | Done | None | `use ValueObject\PlanetId` etc. — falsche Imports (gefixt) |
| 029-techdebt-buildingid-namespace.md | TechDebt | Tech-Debt & Cleanup | Building | Done | None | BuildingId/BuildingType-Namespace gefixt (lagen schon im richtigen Folder) |
| 030-techdebt-deposit-negative.md | TechDebt | Tech-Debt & Cleanup | Resource | Done | None | Extraction clamped + Level-Math: Level 1 = 1× Base |
| 031-techdebt-bootstrap-phpunit.md | TechDebt | Tech-Debt & Cleanup | Common | Done | None | PHPUnit 11 + Smoke-Tests + In-Memory SQLite |
| 032-techdebt-doctrine-orm-mapping.md | TechDebt | Tech-Debt & Cleanup | Common | Done | None | ORM-Mapping aller 5 Entities + Aggregate-Relations + Repos + IT + Initial-Migration |
| 033-techdebt-planet-getresource-fragile.md | TechDebt | Tech-Debt & Cleanup | Planet | Done | None | Collection::getByType/getByTypeOrFail eingeführt, fail-fast |
| 034-web-layer-bootstrap.md | Feature | Web Layer & Auth | User | Open | None | Symfony Web-Layer (Controller, Routes, Error-Pages, Layout) |
| 035-frontend-stack.md | Feature | Web Layer & Auth | User | Open | T-034 | Tailwind + Stimulus + AssetMapper Setup |
| 036-user-entity-registration.md | Feature | Web Layer & Auth | User | Open | None | User-Entity + Registrierung |
| 037-login-logout-security.md | Feature | Web Layer & Auth | User | Open | T-036 | Login/Logout via Symfony Security |
| 038-email-verification.md | Feature | Web Layer & Auth | User | Open | T-036, T-040 | E-Mail-Verifizierung nach Registrierung |
| 039-password-reset.md | Feature | Web Layer & Auth | User | Open | T-036, T-040 | Passwort-Reset Flow |
| 040-mailer-setup.md | Feature | Web Layer & Auth | User | Open | None | Mailer (Mailpit Dev, SMTP Prod) |
| 041-user-profile-settings.md | Feature | Web Layer & Auth | User | Open | T-037 | User-Settings (E-Mail/Passwort ändern, Präferenzen) |
| 042-account-deletion-gdpr.md | Feature | Web Layer & Auth | User | Open | T-041, T-043 | Account-Löschung + Datenexport (DSGVO) |
| 043-user-vs-player-separation.md | Feature | Web Layer & Auth | User | Open | T-036 | User (Account) ≠ Player (Spielfigur) Trennung |
| 044-tick-scheduler.md | Feature | Web Layer & Auth | Tick | Open | None | Tick automatisiert via Cron/Messenger |
| 045-game-dashboard.md | Feature | Game UI | UI | Open | T-035, T-037, T-043 | Hauptansicht für eingeloggten Spieler |
| 046-onboarding-flow.md | Feature | Web Layer & Auth | User | Open | T-037, T-043 | Erstanmeldung → Spielername + Start-Planet |
| 047-in-game-notifications.md | Feature | Game UI | UI | Open | T-043, T-045 | In-Game Benachrichtigungen (Glocken-Icon) |
| 048-security-hardening.md | Feature | Web Layer & Auth | Common | Open | None | Health-Check + Security-Headers + Rate-Limit |
| 049-dev-fixtures.md | Feature | Web Layer & Auth | Common | Open | T-036, T-043 | Doctrine Fixtures (Demo-User + Welt) — User-Teil blockiert von T-036 |
| 049a-world-fixtures.md | Feature | Foundation: Galaxy | SolarSystem | Done | None | doctrine-fixtures-bundle + WorldFixture (5 Systems, deterministische POIs); User defer in T-049 |
| 050-legal-pages.md | Feature | Web Layer & Auth | UI | Open | None | Impressum / Datenschutz / ToS / Cookie-Banner |
| 051-logging-monitoring.md | Feature | Web Layer & Auth | Common | Open | None | Monolog-Channels + optional Sentry |
| 052-allianz-system.md | Feature | Multiplayer | User | Open | T-043, T-024 | Allianzen (Multiplayer) |
| 053-in-game-chat.md | Feature | Multiplayer | User | Open | T-043, T-052 | DM + Allianz-Chat (Multiplayer) |
| 054-public-profile-leaderboard.md | Feature | Multiplayer | User | Open | T-043 | Public Profile + Leaderboard |
| 055-admin-panel.md | Feature | Web Layer & Auth | User | Open | T-037 | EasyAdmin für User + Game-State |
| 056-i18n-setup.md | Feature | Web Layer & Auth | Common | Open | None | DE/EN-Übersetzung Setup |
| 057-domain-events-foundation.md | Feature | Web Layer & Auth | Common | Open | None | Messenger + Domain-Event-Bus + Outbox-Pattern |
| 058-techdebt-docker-compose-mysql.md | TechDebt | Tech-Debt & Cleanup | Common | Done | None | docker-compose auf MySQL 8.0 umgestellt (User-Smoke-Test ausstehend) |
| 059-techdebt-remove-planetcollection.md | TechDebt | Tech-Debt & Cleanup | Planet | Done | None | `PlanetCollection` gelöscht |
| 060-techdebt-tick-persistence.md | TechDebt | Tech-Debt & Cleanup | Tick | Done | None | Tick-Mutationen via TickEngine + `wrapInTransaction` + flush; 2 IT |
| 061-storage-system.md | Feature | Storage Vision | Planet | Done | None | Storage-Cap live-computed (Base+Building); 6 Storage-Bldgs; Cap-Stop Production |
| 062-realtime-construction.md | Feature | Foundation: Buildings | Building | Done | T-009 | Wall-Clock Bauzeit + isReady-Gates + ConstructionCompletionProcessor |
| 063-planet-bonus-system.md | Feature | Foundation: Planet Types | Planet | Done | T-008 | Planet-Type-Boni (Mining-Multi pro Resource je Type) |
| 064-construction-speed-boost.md | Feature | Research & Tech-Tree | Research | Done | T-062, T-025, T-026 | construction_speed_1 (3 Levels) reduziert Bauzeit multiplikativ; Stack mit T-063 Planet-Type |
| 064b-construction-hub-building.md | Feature | Building System | Building | Done | T-064 | CONSTRUCTION_HUB Building (unique, slot-size 2); ×1.10/Level lokaler Speed-Multi, stackt mit T-063 + T-064 |
| 065-energy-system.md | Feature | Energy System | Building | Done | T-006, T-009 | Power-Net pro Planet — Hub-Reaktor + Power-Plants vs Consumer |
| 066-treibstoff-resource.md | Feature | Storage Vision | Resource | Blocked | T-002, T-003 | H2 + Promethium als Fuel-Resources (isFuel-Flag); blocked by T-177 |
| 067-erzeugnis-tree-erweiterung.md | Feature | Resources Tier-2/3 | Resource | Done | T-003 | Tier-2-Erzeugnisse (3 Bars + 5 Compounds) + 2 neue FINITE Erze + Snapshot-Single-Step-Cascade; Volume-Tabelle erweitert. T-072 superseded |
| 068-defense-buildings.md | Feature | Combat & Battle | Building | Done | T-065, T-067 | Shield/Turret/Sensor/AA für Planet-Defense; blocked by T-103 |
| 069-research-lab-tier.md | Feature | Research & Tech-Tree | Research | Done | T-025, T-025c | ResearchNode.requiredLabLevel + Tier-Gate-Validation; Tier-1/2/3-Mapping; LabLevelTooLowException |
| 070-pop-qol-buildings.md | Feature | Pop QoL | Building | Done | T-005, T-006, T-172 | 4 QoL-Foundation: Hospital +20 Pop/Lvl + Cultural-Center +2%/Lvl Mining/Refinement; Effekte in T-070b |
| 070b-pop-qol-effects-extension.md | Feature | Pop QoL | Building | Draft | T-070, T-005 | Hospital-Mangel-Tod + University-RP-Multi + Temple-Loyalty + Power-Consumption (Folge zu T-070) |
| 071-power-plants.md | Feature | Energy System | Building | Draft | T-065 | Solar/Fusion/Antimaterie-Reactor (3 Tiers) |
| 072-erzeugnis-production-buildings.md | Feature | Resources Tier-2/3 | Building | Superseded | T-067 | Durch T-067 abgedeckt; AI-Foundry gehört zu T-115 |
| 073-npc-faction-foundation.md | Feature | NPC Factions | Faction | Done | None | Faction-Domain + lazy Reputation + 4 Seeds (Pirate/Renegade/Xenos/MerchantGuild); Migration + 4 Test-Suiten |
| 074-pirate-encounter-spawn.md | Feature | NPC Factions | Faction | Draft | T-073, T-007, T-017 | Pirat-Random-Encounters in Systemen, Threat-skaliert |
| 075-renegade-xenos-outposts.md | Feature | NPC Factions | POI | Draft | T-073, T-019 | Statische POI-Threats (Renegade-Stronghold/Xenos-Hive/Wormhole-Gate) |
| 076-galaxy-events.md | Feature | Galaxy Events | SolarSystem | Draft | T-007, T-019 | Limited-Time-Galaxy-Events (Warp-Storm, Anomalie, Invasion, Friedensfest) |
| 077-world-boss-raid-target.md | Feature | NPC Factions | Faction | Draft | T-052, T-103, T-074, T-075 | Allianz-PvE-Bosse mit Multi-Player-Damage-Tracking |
| 078-faction-quest-storylines.md | Feature | NPC Factions | Faction | Draft | T-073, T-120 | Faction-Story-Quests mit Reputation-Gates (separate von Onboarding/Daily) |
| 079-spy-heist-infiltration.md | Feature | NPC Factions | Faction | Draft | T-131, T-080 | Spy-Erweiterung — Heist + Long-Term-Infiltration (PvE-only) |
| 080-loot-system.md | Feature | NPC Factions | Faction | Draft | T-073, T-103 | Drop-Tabellen pro Faction, Resource/Tech-Fragment/Blueprint/Cosmetic |
| 081-heimat-schutz-anti-crush.md | Feature | Game Balance | Planet | Done | T-007 | Heimat-Foundation: planets.is_home_planet flag + Auto-Mark beim Start-Claim; Effekte in T-081b |
| 081b-home-protection-effects.md | Feature | Game Balance | Planet | Draft | T-081, T-103, T-080 | Vault/Pop-Loss-Cap/Shield-Cooldown/Sensor-Warning (Hooks für T-103 Battle + T-080 Loot + T-068 Defense + T-161 Notif) |
| 082-interactive-demo-cli.md | Feature | Demo CLI | Demo | Done | T-001, T-023, T-073, T-085, T-016, T-017, T-019, T-020, T-022, T-151 | Sandbox-CLI `app:demo:run` mit Choice-Menü für alle Game-Actions + Time-Travel |
| 082b-demo-cli-ux-polish.md | Feature | Demo CLI | Demo | Done | T-082 | Galaxy-Overview + Cost-Preview + Demo-Buff (Hub L1, 300 W/F/O) + Wormhole-Garantie |
| 082c-demo-goals.md | Feature | Demo CLI | Demo | Done | T-082b, T-021, T-014 | 5 fixe Demo-Goals (Hub L2, Mines, Recycling, Debris, 2. Planet) als On-Demand-Check |
| 082d-demo-action-log.md | Feature | Demo CLI | Demo | Done | T-082, T-082b, T-082c | DemoActionLogger + StateSnapshotter + Export-Menu (JSONL, vollständige Snapshots, Backup-on-Reset) |
| 082e-demo-start-resource-buff.md | Feature | Demo CLI | Demo | Done | T-082b | Start-Buff: 3000 IRON_ORE + 800 COAL + 400 CU + 300 SI + 200 IRON_BAR + 1500 W/F/O |
| 082f-demo-action-log-details.md | Bug | Demo CLI | Demo | Done | None | Action-Log liefert pro Action konkrete Params (building_type, ship_type, fleet_id, etc.) |
| 084-galactic-council.md | Feature | Endgame | Faction | Draft | T-052, T-121, T-130 | Endgame-Influence-Voting auf Crusade-Targets / Galaxy-Boni |
| 085-wormhole-poi.md | Feature | POI System | POI | Done | T-019 | Wormhole POI-Subtype mit Pair-Verlinkung + Galaxy-Pair-Spawn (Foundation) |
| 086-black-hole-poi.md | Feature | POI System | POI | Draft | T-019, T-027 | Schwarzes-Loch + Antimaterie-Harvest (Tech-gated) |
| 087-fog-of-war.md | Feature | Exploration & Probes | Probe | Draft | T-018, T-007, T-013, T-019 | Player-spezifische Discovery-Levels (UNKNOWN/METADATA/SCANNED) |
| 088-combat-munition.md | Feature | Combat & Battle | Resource | Ready | T-067, T-103, T-102, T-178 | BALLISTIC_AMMO/WARHEAD/PLASMA_CHARGE Verbrauch im Battle |
| 089-luxury-civilian-goods.md | Feature | Resources Tier-2/3 | Resource | Draft | T-005, T-067 | Pop-Tier-Layer (Working/Middle/Upper) + Civilian-Goods |
| 090-medicine-bio-resources.md | Feature | Resources Tier-2/3 | Resource | Draft | T-005, T-070 | BIOMASS/PHARMA/VACCINE/CYBERNETIC-IMPLANT |
| 091-tier3-combat-components.md | Feature | Resources Tier-2/3 | Resource | Draft | T-067, T-115, T-102 | TARGETING_COMPUTER/REACTIVE_ARMOR/PLASMA_COIL/ECM/WARP_DRIVE_CORE |
| 092-rare-exotic-resources.md | Feature | Resources Tier-2/3 | Resource | Draft | T-115, T-080, T-086 | XENOS_ARTIFACT/WARP_CRYSTAL/DARK_MATTER/VOID_ESSENCE/ANCIENT_DATA_CORE |
| 093-alliance-raumstation.md | Feature | Multiplayer | POI | Draft | T-023, T-052 | Allianz-Raumstation pro System (Repair/Trade/Defense-Hub) |
| 094-build-queue.md | Feature | Building System | Building | Done | T-009, T-062 | Parallel-Slot-Model (max 3); Cancel/Refund/Hub-Bonus/Forschungs-Slots deferred |
| 094b-build-queue-cancel-refund.md | Feature | Building System | Building | Done | T-094 | CancelBuildCommand: Initial=Delete, Upgrade=Level-1; 50% Resource + 100% Pop Refund |
| 094c-build-queue-slot-extensions.md | Feature | Building System | Building | Done | T-094, T-025 | HQ-Level +1 Parallel-Slot pro Lvl-5 (Cap 8); Logistics-Forschung in T-094d split |
| 094d-build-queue-logistics-research.md | Feature | Building System | Building | Done | T-094, T-094c, T-025 | logistics_1 (3 Lvl, +1 Slot/Lvl) + BuildQueueCapCalculator; HQ+Logistics stack, Hard-Cap 8 |
| 095-auto-production-routing.md | Feature | Trade & Economy | Trade | Draft | T-110 | Threshold-getriggerte Auto-Trade-Routes — Folge T-110 |
| 096-player-history-stats.md | Feature | Player Progression | Player | Done | T-014, T-009, T-012 | Stats-Foundation: 3 Player-Counter (buildings/planets/ships) + Hooks in 3 Command-Services; Tick-/Battle-Counters in T-096b |
| 096b-stats-extension.md | Feature | Player Progression | Player | Draft | T-096, T-103, T-080 | Resource-Mining-Total + Battle-Counters + FactionRep-Lifetime + XP-Aggregation (Folge zu T-096) |
| 097-pop-tier-buildings.md | Feature | Pop QoL | Building | Draft | T-005, T-070, T-089, T-090 | GENEBANK/CLONING_VAT/CIVIC_CENTER/AGRI_DOME/WATER_RECLAIMER/ATMOSPHERIC_PROCESSOR |
| 097a-renewable-production-buildings.md | Feature | Pop QoL | Building | Done | T-009, T-061, T-062 | 3 Tier-0 Producer (WATER_RECLAIMER, AGRI_DOME, ATMOSPHERIC_PROCESSOR) + Processor; Pop-Survival selbsttragend |
| 098-specialist-tracks.md | Feature | Player Progression | Player | Draft | T-025, T-046 | 5 Specialist-Tracks PERMANENT (+30%/-10% + Branch-Lock) |
| 099-threat-skalierung.md | Feature | NPC Factions | Faction | Draft | T-074, T-075, T-096 | PlayerScore → PvE-Encounter-Difficulty-Adaptiv |
| 100-trade-hub-buildings.md | Feature | Trade & Economy | Building | Blocked | T-110, T-111, T-112, T-177 | MARKETPLACE/SPACEPORT/CUSTOMS_HOUSE/WAREHOUSE/BAZAAR; blocked by T-177 |
| 101-planet-cap-per-player.md | Feature | Game Balance | Planet | Done | T-014, T-094d | Planet-Cap Foundation: BASE 5 + logistics_1 Bonus, HARD_CAP 10; Colonize-Cap-Check; Demo-Status N/M |
| 101b-planet-abandon-mechanic.md | Feature | Game Balance | Planet | Draft | T-101 | Planet-Abandon-Mechanik (Folge zu T-101) — Resources/Buildings reset + Slot frei |
| 102-ship-classes-foundation.md | Feature | Combat & Battle | Ship | Done | T-011, T-012, T-067, T-104a | 5 Combat × 3 Mk + 4 Spezial-Klassen, hohe Cost, Captain-Required |
| 103-battle-resolution-engine.md | Feature | Combat & Battle | Ship | Done | T-102, T-068 | Round-based Auto-Resolution + Tactic-Counter (RPS-light) |
| 103b-tactic-rps-system.md | Feature | Combat & Battle | Ship | Draft | T-103 | — |
| 103c-npc-ai-tactic-heuristik.md | Feature | Combat & Battle | Ship | Draft | T-103b | — |
| 103d-battle-replay-persistence.md | Feature | Combat & Battle | Ship | Draft | T-103 | — |
| 103e-loot-trigger-hook.md | Feature | Combat & Battle | Ship | Draft | T-103, T-080 | — |
| 104a-crew-foundation.md | Feature | Combat & Battle | Ship | Done | T-009, T-070 | Akademie + Officer-Quarters + Captain-Crew-Type |
| 104b-captain-skill-trees.md | Feature | Combat & Battle | Ship | Done | T-104a, T-103 | Beam-Master/Missile-Spec/Shield-Tactician/Fleet-Commander |
| 104c-other-crew-roles.md | Feature | Combat & Battle | Ship | Ready | T-104a, T-110 | Forscher/Engineer/Diplomat (Lab-Boost/Maintenance/Reputation) |
| 105-ship-maintenance.md | Feature | Ships & Fleet | Ship | Blocked | T-066, T-102, T-005, T-178, T-179 | Treibstoff + Crew-Versorgung; Stranding bei Mangel; blocked by T-178 |
| 106-diplomatic-buildings.md | Feature | Resources Tier-2/3 | Building | Draft | T-073, T-052, T-104c | EMBASSY/COMM_ARRAY/CULTURAL_MISSION/INTELLIGENCE_HQ/TRANSLATOR_BUREAU |
| 107-manufacturing-buildings.md | Feature | Resources Tier-2/3 | Building | Draft | T-067, T-088, T-089, T-090, T-091, T-115 | Bündel: ~20 Manufacturing-Buildings (Munition/Civilian/Bio/Tier-3-Combat/Tier-3-Resources) |
| 108-specialty-mining-buildings.md | Feature | Resources Tier-2/3 | Building | Draft | T-002, T-019, T-020, T-066 | ASTEROID_DRONE/DEEP_DRILL/ATMOSPHERIC_HARVESTER/ICE_DRILLER/VOLCANIC_TAPPER/LUNAR_PROCESSOR |
| 109-tier3-containment-storage.md | Feature | Storage Vision | Building | Blocked | T-115, T-092, T-177, T-061 | ANTIMATTER_CONTAINMENT/AI_CORE_VAULT/ADAMANTIUM_DEPOT/VOID_CONTAINER/ARTIFACT_VAULT; blocked by T-177 |
| 110-trade-routes.md | Feature | Trade & Economy | Trade | Ready | T-015, T-014 | Auto-Transport eigene Planeten — Schiff-bound Routes |
| 110b-route-refill-logic.md | Feature | Trade & Economy | Trade | Draft | T-110, T-088, T-105 | — |
| 111-auction-house.md | Feature | Trade & Economy | Trade | Draft | T-110, T-073 | Galaxy-weit Auction + Lieferzeit via Transportschiff |
| 112-hybrid-pricing.md | Feature | Trade & Economy | Trade | Draft | T-111, T-073, T-007, T-023 | Need-Based-Pricing + Statische Handelsposten (Inventory-bound, nicht-übernehmbar) |
| 113-black-market.md | Feature | Trade & Economy | Trade | Draft | T-131, T-073 | Renegade-Rep-Path → illegale Tech + Tier-3-Resources |
| 114-roving-trader-ships.md | Feature | Trade & Economy | Ship | Draft | T-112, T-073, T-007 | NPC-Trader-Schiffe Spawn/Despawn-Cycle (magic-spawn, kein Echtzeit-Movement) |
| 115-tier-3-resources.md | Feature | Resources Tier-2/3 | Resource | Draft | T-067, T-086 | Plasteel/Adamantium/Plasma-Cell/AI-Core/Antimaterie |
| 116-mega-structures-foundation.md | Feature | Mega Structures | Mega | Draft | T-115, T-027, T-052 | Genesis-Forge first; Allianz-built; Single-Use-Terraform |
| 117-alliance-research-community-goal.md | Feature | Multiplayer | Research | Draft | T-069, T-052, T-098 | Direkt-Donate-RP-an-Tech, kein zentraler Pool |
| 118-region-trade-arbitrage.md | Feature | Trade & Economy | Trade | Draft | T-112, T-114, T-007 | Galaxy-Trade-Regionen (Core/Civilian/Border/Frontier) mit Need-Profilen + Pricing-Bias |
| 119-mega-dyson-sphere.md | Feature | Mega Structures | Mega | Draft | T-116, T-115 | Mega-Folge: Dyson-Sphäre Power-Bonus |
| 120-quest-engine-onboarding.md | Feature | Quests & Engagement | Quest | Draft | T-046, T-073 | Quest-Engine-Foundation + 10 Onboarding-Quests |
| 121-crusade-system.md | Feature | Endgame | Crusade | Draft | T-077, T-052, T-076 | 6-Wochen-Cycle, World-Boss + Top-3-Title (KEIN Mandate) |
| 122-player-background.md | Feature | Player Progression | Player | Done | T-073 | Background-Foundation: 5-Enum + nullable Field + permanent Setter + Demo-CLI Action; Effect-Resolver in T-122b |
| 122b-background-effect-resolver.md | Feature | Player Progression | Player | Draft | T-122, T-073 | 7 Multiplier-Hooks (Mining/Rep/RP/Pop/Ship/Probe/Trade) + Stack-Reihenfolge mit T-098 |
| 123-player-xp-career.md | Feature | Player Progression | Player | Draft | T-096 | Level 1-100, asymptotischer XP-Cost, Skill-Slot-Reservation |
| 124-mega-wormhole-generator.md | Feature | Mega Structures | Mega | Draft | T-116, T-085 | Mega-Folge: Künstliches Wurmloch zwischen 2 frei-gewählten Systems |
| 125-mega-stargate.md | Feature | Mega Structures | Mega | Draft | T-116, T-124 | Mega-Folge: Stargate-Network für Multi-Endpoint-Travel |
| 126-skill-slot-implementation.md | Feature | Player Progression | Player | Draft | T-123 | Skill-Pick-Mechanik pro Player-Level (Folge T-123) |
| 127-tech-mining-industry-branch.md | Feature | Research & Tech-Tree | Research | Draft | T-025, T-002, T-067, T-115 | Tech-Branch Mining/Industrie (5 Tier × 2 Nodes = 10) |
| 128-tech-shipbuilding-branch.md | Feature | Research & Tech-Tree | Research | Draft | T-025, T-011, T-102, T-091 | Tech-Branch Schiffbau (Mark-Tier-Unlock + Klassen-Lock) |
| 129-tech-energy-branch.md | Feature | Research & Tech-Tree | Research | Draft | T-025, T-065, T-066, T-071 | Tech-Branch Energie (Solar→Fusion→Antimatter→Zero-Point) |
| 130-alliance-treaties.md | Feature | Multiplayer | User | Draft | T-052, T-121, T-110 | Crusade-Coalition + Resource-Federation MVP |
| 131-spy-system-foundation.md | Feature | NPC Factions | Faction | Draft | T-073, T-075, T-104c | Spy-Network + Recon/Sabotage gegen NPC-Outposts (PvE-only) |
| 132-alliance-research-pact.md | Feature | Multiplayer | Research | Draft | T-130, T-117 | Cross-Alliance-Research-Donation (Treaty-Folge zu T-130) |
| 133-alliance-defense-coalition.md | Feature | Multiplayer | User | Draft | T-130, T-153, T-068 | Joint-Defense gegen Outpost-Threats (Treaty-Folge zu T-130) |
| 134-tech-cybernetics-branch.md | Feature | Research & Tech-Tree | Research | Draft | T-025, T-069, T-090, T-091 | Tech-Branch Kybernetik (AI-Core/Neural-Network/Cybernetic-Singularity) |
| 135-tech-diplomacy-branch.md | Feature | Research & Tech-Tree | Research | Draft | T-025, T-073, T-104c, T-106 | Tech-Branch Diplomatie (Embassy/Treaties/Pax-Imperialis) |
| 136-tech-logistics-branch.md | Feature | Research & Tech-Tree | Research | Draft | T-025, T-094, T-110, T-101 | Tech-Branch Logistik (Bau-Queue/Trade-Routes/Planet-Cap-Skalierung) |
| 137-tech-defense-branch.md | Feature | Research & Tech-Tree | Research | Draft | T-025, T-068, T-081 | Tech-Branch Defense (Shield/Turret/Fortress-Doctrine) |
| 138-tech-xenobiology-branch.md | Feature | Research & Tech-Tree | Research | Draft | T-025, T-073, T-090, T-092 | Tech-Branch Xenobiologie (Xenos-Loot/Hybrid-Genetics/Genetic-Renaissance) |
| 139-tech-tree-master-design.md | Feature | Research & Tech-Tree | Research | Draft | T-025, T-026, T-027, T-127, T-128, T-129, T-134, T-135, T-136, T-137, T-138 | Meta-Ticket: alle 10 Tech-Branches + Branch-Lock-Konsistenz |
| 140-daily-weekly-quests.md | Feature | Quests & Engagement | Quest | Draft | T-120, T-096 | 3 Daily + 1 Weekly Quest-Generierung adaptiv |
| 141-achievement-system.md | Feature | Quests & Engagement | Player | Draft | T-096 | 100+ Achievements, NUR Cosmetic (Title/Banner) |
| 142-login-streak.md | Feature | Quests & Engagement | Player | Draft | T-037 | 7-Tage-Cycle Login-Belohnung, Cycle wiederholt |
| 143-cosmetics-inventory-system.md | Feature | Player Progression | Player | Draft | T-141, T-080 | Title/Banner/Frame/Schiff-Skin/Building-Skin Equip-Slots |
| 150-bubble-protection.md | Feature | Game Balance | Player | Done | T-014 | Bubble-Foundation: PlayerBubbleStatus + Auto-Exit nach 2. Planet-Claim; Effekte+CatchUp in T-150b |
| 150b-bubble-effects-and-catchup.md | Feature | Game Balance | Player | Draft | T-150, T-074 | Bubble-Skip-Effekte in T-074/T-075/T-111/T-160 + Catch-Up-Mining-Multiplier 14d ×1.5 |
| 151-soft-cap-diminishing-returns.md | Feature | Game Balance | Common | Done | T-005, T-009, T-061 | SoftCapConfig (Pop/Building-Lvl/Stockpile) + 3 Hook-Stellen |
| 152-inactivity-protection.md | Feature | Game Balance | Player | Draft | T-037, T-074 | 7d/30d/90d-Stages + Vacation-Welcome-Pack |
| 153-alliance-rescue.md | Feature | Multiplayer | User | Draft | T-052, T-105 | Stranded-Rescue + Defense-Reinforcement zwischen Allianz-Members |
| 160-galaxy-map.md | Feature | Game UI | UI | Draft | T-034, T-035, T-087 | Interaktive Map (SVG-MVP, Stimulus-controlled, Fog-of-War) |
| 161-notifications.md | Feature | Game UI | UI | Draft | T-034, T-047 | In-Game-Bell + optional PWA-Web-Push |
| 162-build-templates.md | Feature | Game UI | Building | Draft | T-094, T-034 | Build-Templates speichern/applizieren auf neue Planeten |
| 163-strategy-forecast-dashboard.md | Feature | Game UI | UI | Draft | T-045 | Resource-Forecast/Build-ETAs/ROI-Calculator/Threat-Indicator |
| 164-battle-replay-ui.md | Feature | Game UI | UI | Draft | T-103 | Battle-Replay (Table-MVP, Animated-Folge) |
| 165-settings-personalization.md | Feature | Game UI | UI | Draft | T-041 | UI-Theme/Default-Tactic/Galaxy-Filter/Notification-Prefs |
| 166-animated-battle-replay.md | Feature | Game UI | UI | Draft | T-164, T-103 | Animated-Battle-Replay-View (Folge zu T-164) |
| 167-cleanup-loose-ends.md | TechDebt | Tech-Debt & Cleanup | Common | Done | None | Status-Sync, Stale-Stubs raus, 6 Folge-Drafts (T-026b/c, T-064b, T-094b/c, T-015c), T-024 superseded |
| 168-demo-cli-env-preflight.md | Bug | Tech-Debt & Cleanup | Demo | Done | None | `app:demo:run` Auto-Re-Exec in demo-Env via Symfony\Process — kein --env=demo mehr nötig |
| 169-demo-reset-action-bug.md | Bug | Tech-Debt & Cleanup | Demo | Done | None | Reset-Action zentralisiert via bootstrapFreshPlayer + pendingPlayerSwap; Buff+Galaxy konsistent, Loop schwenkt um |
| 170-tech-tree-building-research-gating.md | Feature | Research & Tech-Tree | Research | Done | T-009, T-025 | Tech-Tree Foundation: Buildings via Research locked, Research via Building-Vorhandensein gated; 6 Tier-1-Nodes, 13 Buildings gated |
| 171-building-uniqueness-slot-concept.md | Feature | Building System | Building | Done | T-009, T-094, T-170 | 6 Strategic-Unique + Slot-Size pro Building (1-3) + Slot-Cap pro PlanetSize (8-40) |
| 172-hq-vs-hub-refactor.md | Feature | Building System | Building | Done | T-006, T-171 | HQ (unique, slot-3, +25 Pop, Storage, Slot-Bonus capped); HUB (multi, slot-1, +100 Pop); CONSTRUCTION_YARD rename |
| 173-hq-building-level-cap.md | Feature | Building System | Building | Ready | T-172 | HQ-Level cappt Building-Level + steile HQ-Cost-Curve + lokaler Build-Speed-Bonus |
| 174-deprecate-station-build.md | TechDebt | Tech-Debt & Cleanup | POI | Done | T-175 | Station-Build soft-deprecated: wirft `StationConstructionDeprecatedException`; 6 orphan POI-Exceptions entfernt; Hard-Remove erst nach T-175 |
| 175-pirate-station-spawn.md | Feature | NPC Factions | POI | Draft | T-073, T-049a, T-023 | Galaxy-Bootstrap-Spawn von Pirate-owned + ABANDONED Stations (Lost-Tech-Lore) |
| 176-pirate-station-takeover.md | Feature | NPC Factions | POI | Draft | T-023b, T-073, T-103 | Pirate-Takeover ABANDONED → Pirate + Combat-Capture-Mechanik (zweistufig) |
| 177-generic-storage-refactor.md | Feature | Storage Vision | Planet | Done | T-180 | Generic Volume-Storage Planet (m³) + WAREHOUSE konsolidiert 6 T-061-Buildings; Volume-Cap-Stop für Production; canAddItem/maxAddableQuantity. T-061 superseded |
| 178-ship-cargo-universal.md | Feature | Storage Vision | Ship | Blocked | T-177, T-180 | Ship-Cargo-Universal: alle Schiffe haben Cargo-Volume; blocked by T-177 |
| 179-pop-as-storage-item.md | Feature | Storage Vision | Planet | Blocked | T-177, T-178, T-180 | Pop wird Storage-Item mit hohem Size-Multi; T-004/T-005 refactored; blocked by T-178 |
| 180-resource-volume-config.md | Feature | Storage Vision | Resource | Done | None | ResourceVolumeConfig (m³/Unit für 14 ResourceTypes + Pop=10); Foundation für T-177ff Generic-Storage |
| 181-resource-volume-debug-command.md | Feature | Storage Vision | Resource | Done | T-180 | `app:debug:resource-volume` Symfony-Command — Volume-Tabelle sortiert + Beispiel-Profile |
| 182-techdebt-remove-university-building.md | TechDebt | Tech-Debt & Cleanup | Building | Done | None | UNIVERSITY-Building entfernt (Wort-Mix-Up mit RESEARCH_LAB); T-070-AC + T-070b-Section + T-089/T-097a Refs aufgeräumt |
| 183-station-generic-storage.md | Feature | Storage Vision | POI | Blocked | T-180, T-177, T-023, T-023b | Station-Generic-Volume-Storage (analog T-177 Planet); blocked by T-177 + T-180 |
