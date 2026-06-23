# Tickets

| File | Type | Status | Summary |
|------|------|--------|---------|
| 001-renewable-resources.md | Feature | Done | WATER/FOOD/OXYGEN als ResourceType; Start-Amount 100; Base-Werte gesetzt |
| 002-finite-resources-extend.md | Feature | Done | 6 Erze + 6 Mines + Base-Werte gestaffelt; canProduce-Bug gefixt |
| 003-erzeugnis-eisenbarren.md | Feature | Done | ResourceCategory + IRON_BAR + IRON_SMELTER + Refinement-Tick (2:1:1) (T-167 Status-Sync) |
| 004-population-entity.md | Feature | Done | Embeddable Population (total/assigned/cap) auf Planet + Migration |
| 005-population-consumption.md | Feature | Done | Pop-Verbrauch W/F + Logistic Growth + Mangel-Kill (free first) |
| 006-hub-building.md | Feature | Done | HUB BuildingType + Cap +50/Level + Auto-Recalc in addBuilding |
| 007-sonnensystem-domain.md | Feature | Done | SolarSystem-Entity + Planet→System + 5-System-Galaxy beim Claim |
| 008-planet-types-sizes.md | Feature | Done | 5 Sizes + 7 Types + Consumption-Multi + Type-basierte Deposits |
| 009-building-cost-construction.md | Feature | Done | BuildingCost + BuildBuildingCommand + Pop-Bindung; Echtzeit-Stub |
| 010-building-upgrade.md | Feature | Done | UpgradeBuildingCommand + 2^level Skalierung + Cap-Recalc |
| 011-raumwerft.md | Feature | Done | SHIPYARD BuildingType + Cost/Duration + Planet::getShipyardLevel/hasShipyard |
| 012-raumschiff-base.md | Feature | Done | Ship-Foundation + BuildShipCommand + ShipSupplyProcessor; ShipType-Stub GENERIC |
| 013-sonde-types.md | Feature | Done | Probe-Domain (SYSTEM/ORBITAL/DEEP_SCAN) + PROBE_LAB Building + BuildProbeCommand |
| 014-kolonisationsschiff.md | Feature | Done | COLONY_SHIP + ColonizePlanetCommand mit Pop-Transfer + ShipCostConfig-Refactor |
| 015-transportschiff.md | Feature | Done | 3 Transport-Klassen + CargoManifest Embeddable + Load/Unload/DockCommands |
| 015b-station-cargo-transfer.md | Feature | Done | Ship.station-Field + LoadCargo/UnloadCargo branch für Station-Storage; Pop-Transfer skip |
| 015c-station-pop-transfer.md | Feature | Done | Pop-Transfer Ship ↔ Station via station.populationOnStation; Cap-Check defer T-023b |
| 016-bergungsschiff.md | Feature | Done | ShipType::SALVAGE + Echtzeit-Salvage (50 Units/min) für AsteroidField + Field-Cleanup |
| 017-flotte-movement.md | Feature | Done | Persistent-Fleet + Wallclock-Movement (Slowest-Ship-Speed) + FleetArrivalService; Magic-Dock-Cleanup |
| 017b-fleet-movement-modifiers.md | Feature | Done | Wormhole-Travel ×0.2 wenn Pair zw. Systemen + Player hat Wormhole-Tech; Fallback ohne Tech = normal |
| 018-teleskop-discovery.md | Feature | Done | TELESCOPE-Building + PlayerSystemDiscovery + Tick-Reveal; Demo-Galaxy-Overview filtert auf entdeckte |
| 019-poi-system.md | Feature | Done | POI-Foundation (STI) + 7 PoiTypes + SolarSystem.pois |
| 020-asteroidenfeld.md | Feature | Done | AsteroidField POI-Subtype (STI) + Galaxy-Spawn 0-2 pro System mit FINITE-Erzen |
| 021-truemmerfeld-recycling.md | Feature | Done | DebrisField POI + DEBRIS-ResourceTypes + RECYCLING_PLANT + RecyclingProcessor; Spawn via killShip + Fixture |
| 022-nebel-poi.md | Feature | Done | Nebula POI-Subtype (STI) + concealmentLevel + 30%-Galaxy-Spawn |
| 023-raumstation.md | Feature | Done | SpaceStation POI (max 1/System, Shipyard-L3-Gate, Storage 100k); Maintenance/Übernahme = T-023b |
| 023b-station-maintenance-takeover.md | Feature | Draft | Station-Maintenance-Tick + Pop-Mortality + ABANDONED-State + ClaimAbandonedStationCommand |
| 024-raumschlacht.md | Feature | Superseded | Abgelöst durch T-103 (T-167 Cleanup) |
| 025-forschung-framework.md | Feature | Done | Wallclock-Forschung Foundation: Node + Active/PlayerResearch + RESEARCH_LAB-Building + Demo-Action + Stub-Nodes |
| 025b-multi-lab-research-boost.md | Feature | Done (superseded T-025c) | Auto-Aggregator (geometric decay 0.5); wird durch T-025c-Opt-In-Modell ersetzt |
| 025c-multi-lab-opt-in-with-cost.md | Feature | Done | Opt-In Multi-Lab beim StartResearch: Geometric-Decay-Bonus + Flat/Quadratic-Cost-Penalty; JSON-Persistence frozen-at-start |
| 026-antriebstechnologie-tree.md | Feature | Done | 7 Antriebs-Nodes + Inter-System-Travel-Lock (ftl_hyperdrive); PropulsionType/Fuel via Folge |
| 026b-wormhole-tech-validation.md | Feature | Superseded | Durch T-017b absorbiert (Fallback-Semantik statt Hard-Block, User-Decision) |
| 026c-propulsion-type-field.md | Feature | Done | Ship.propulsion (7 types) + research-gate beim Build + Speed-Multiplier-Stack mit ShipType |
| 027-planetologie-research.md | Feature | Open | Planetologie-Forschung (Sondendetails + Terraform-Gate) |
| 028-techdebt-wrong-namespaces.md | TechDebt | Done | `use ValueObject\PlanetId` etc. — falsche Imports (gefixt) |
| 029-techdebt-buildingid-namespace.md | TechDebt | Done | BuildingId/BuildingType-Namespace gefixt (lagen schon im richtigen Folder) |
| 030-techdebt-deposit-negative.md | TechDebt | Done | Extraction clamped + Level-Math: Level 1 = 1× Base |
| 031-techdebt-bootstrap-phpunit.md | TechDebt | Done | PHPUnit 11 + Smoke-Tests + In-Memory SQLite |
| 032-techdebt-doctrine-orm-mapping.md | TechDebt | Done | ORM-Mapping aller 5 Entities + Aggregate-Relations + Repos + IT + Initial-Migration |
| 033-techdebt-planet-getresource-fragile.md | TechDebt | Done | Collection::getByType/getByTypeOrFail eingeführt, fail-fast |
| 034-web-layer-bootstrap.md | Feature | Open | Symfony Web-Layer (Controller, Routes, Error-Pages, Layout) |
| 035-frontend-stack.md | Feature | Open | Tailwind + Stimulus + AssetMapper Setup |
| 036-user-entity-registration.md | Feature | Open | User-Entity + Registrierung |
| 037-login-logout-security.md | Feature | Open | Login/Logout via Symfony Security |
| 038-email-verification.md | Feature | Open | E-Mail-Verifizierung nach Registrierung |
| 039-password-reset.md | Feature | Open | Passwort-Reset Flow |
| 040-mailer-setup.md | Feature | Open | Mailer (Mailpit Dev, SMTP Prod) |
| 041-user-profile-settings.md | Feature | Open | User-Settings (E-Mail/Passwort ändern, Präferenzen) |
| 042-account-deletion-gdpr.md | Feature | Open | Account-Löschung + Datenexport (DSGVO) |
| 043-user-vs-player-separation.md | Feature | Open | User (Account) ≠ Player (Spielfigur) Trennung |
| 044-tick-scheduler.md | Feature | Open | Tick automatisiert via Cron/Messenger |
| 045-game-dashboard.md | Feature | Open | Hauptansicht für eingeloggten Spieler |
| 046-onboarding-flow.md | Feature | Open | Erstanmeldung → Spielername + Start-Planet |
| 047-in-game-notifications.md | Feature | Open | In-Game Benachrichtigungen (Glocken-Icon) |
| 048-security-hardening.md | Feature | Open | Health-Check + Security-Headers + Rate-Limit |
| 049-dev-fixtures.md | Feature | Open | Doctrine Fixtures (Demo-User + Welt) — User-Teil blockiert von T-036 |
| 049a-world-fixtures.md | Feature | Done | doctrine-fixtures-bundle + WorldFixture (5 Systems, deterministische POIs); User defer in T-049 |
| 050-legal-pages.md | Feature | Open | Impressum / Datenschutz / ToS / Cookie-Banner |
| 051-logging-monitoring.md | Feature | Open | Monolog-Channels + optional Sentry |
| 052-allianz-system.md | Feature | Open | Allianzen (Multiplayer) |
| 053-in-game-chat.md | Feature | Open | DM + Allianz-Chat (Multiplayer) |
| 054-public-profile-leaderboard.md | Feature | Open | Public Profile + Leaderboard |
| 055-admin-panel.md | Feature | Open | EasyAdmin für User + Game-State |
| 056-i18n-setup.md | Feature | Open | DE/EN-Übersetzung Setup |
| 057-domain-events-foundation.md | Feature | Open | Messenger + Domain-Event-Bus + Outbox-Pattern |
| 058-techdebt-docker-compose-mysql.md | TechDebt | Done | docker-compose auf MySQL 8.0 umgestellt (User-Smoke-Test ausstehend) |
| 059-techdebt-remove-planetcollection.md | TechDebt | Done | `PlanetCollection` gelöscht |
| 060-techdebt-tick-persistence.md | TechDebt | Done | Tick-Mutationen via TickEngine + `wrapInTransaction` + flush; 2 IT |
| 061-storage-system.md | Feature | Done | Storage-Cap live-computed (Base+Building); 6 Storage-Bldgs; Cap-Stop Production |
| 062-realtime-construction.md | Feature | Done | Wall-Clock Bauzeit + isReady-Gates + ConstructionCompletionProcessor |
| 063-planet-bonus-system.md | Feature | Done | Planet-Type-Boni (Mining-Multi pro Resource je Type) |
| 064-construction-speed-boost.md | Feature | Done | construction_speed_1 (3 Levels) reduziert Bauzeit multiplikativ; Stack mit T-063 Planet-Type |
| 064b-construction-hub-building.md | Feature | Done | CONSTRUCTION_HUB Building (unique, slot-size 2); ×1.10/Level lokaler Speed-Multi, stackt mit T-063 + T-064 |
| 065-energy-system.md | Feature | Draft | Power-Net pro Planet — Hub-Reaktor + Power-Plants vs Consumer |
| 066-treibstoff-resource.md | Feature | Blocked | H2 + Promethium als Fuel-Resources (isFuel-Flag); blocked by T-177 |
| 067-erzeugnis-tree-erweiterung.md | Feature | Done | Tier-2-Erzeugnisse (3 Bars + 5 Compounds) + 2 neue FINITE Erze + Snapshot-Single-Step-Cascade; Volume-Tabelle erweitert. T-072 superseded |
| 068-defense-buildings.md | Feature | Blocked | Shield/Turret/Sensor/AA für Planet-Defense; blocked by T-103 |
| 069-research-lab-tier.md | Feature | Done | ResearchNode.requiredLabLevel + Tier-Gate-Validation; Tier-1/2/3-Mapping; LabLevelTooLowException |
| 070-pop-qol-buildings.md | Feature | Done | 4 QoL-Foundation: Hospital +20 Pop/Lvl + Cultural-Center +2%/Lvl Mining/Refinement; Effekte in T-070b |
| 070b-pop-qol-effects-extension.md | Feature | Draft | Hospital-Mangel-Tod + University-RP-Multi + Temple-Loyalty + Power-Consumption (Folge zu T-070) |
| 071-power-plants.md | Feature | Draft | Solar/Fusion/Antimaterie-Reactor (3 Tiers) |
| 072-erzeugnis-production-buildings.md | Feature | Superseded | Durch T-067 abgedeckt; AI-Foundry gehört zu T-115 |
| 073-npc-faction-foundation.md | Feature | Done | Faction-Domain + lazy Reputation + 4 Seeds (Pirate/Renegade/Xenos/MerchantGuild); Migration + 4 Test-Suiten |
| 074-pirate-encounter-spawn.md | Feature | Draft | Pirat-Random-Encounters in Systemen, Threat-skaliert |
| 075-renegade-xenos-outposts.md | Feature | Draft | Statische POI-Threats (Renegade-Stronghold/Xenos-Hive/Wormhole-Gate) |
| 076-galaxy-events.md | Feature | Draft | Limited-Time-Galaxy-Events (Warp-Storm, Anomalie, Invasion, Friedensfest) |
| 077-world-boss-raid-target.md | Feature | Draft | Allianz-PvE-Bosse mit Multi-Player-Damage-Tracking |
| 078-faction-quest-storylines.md | Feature | Draft | Faction-Story-Quests mit Reputation-Gates (separate von Onboarding/Daily) |
| 079-spy-heist-infiltration.md | Feature | Draft | Spy-Erweiterung — Heist + Long-Term-Infiltration (PvE-only) |
| 080-loot-system.md | Feature | Draft | Drop-Tabellen pro Faction, Resource/Tech-Fragment/Blueprint/Cosmetic |
| 081-heimat-schutz-anti-crush.md | Feature | Done | Heimat-Foundation: planets.is_home_planet flag + Auto-Mark beim Start-Claim; Effekte in T-081b |
| 081b-home-protection-effects.md | Feature | Draft | Vault/Pop-Loss-Cap/Shield-Cooldown/Sensor-Warning (Hooks für T-103 Battle + T-080 Loot + T-068 Defense + T-161 Notif) |
| 082-interactive-demo-cli.md | Feature | Done | Sandbox-CLI `app:demo:run` mit Choice-Menü für alle Game-Actions + Time-Travel |
| 082b-demo-cli-ux-polish.md | Feature | Done | Galaxy-Overview + Cost-Preview + Demo-Buff (Hub L1, 300 W/F/O) + Wormhole-Garantie |
| 082c-demo-goals.md | Feature | Done | 5 fixe Demo-Goals (Hub L2, Mines, Recycling, Debris, 2. Planet) als On-Demand-Check |
| 082d-demo-action-log.md | Feature | Done | DemoActionLogger + StateSnapshotter + Export-Menu (JSONL, vollständige Snapshots, Backup-on-Reset) |
| 082e-demo-start-resource-buff.md | Feature | Done | Start-Buff: 3000 IRON_ORE + 800 COAL + 400 CU + 300 SI + 200 IRON_BAR + 1500 W/F/O |
| 082f-demo-action-log-details.md | Bug | Done | Action-Log liefert pro Action konkrete Params (building_type, ship_type, fleet_id, etc.) |
| 097a-renewable-production-buildings.md | Feature | Done | 3 Tier-0 Producer (WATER_RECLAIMER, AGRI_DOME, ATMOSPHERIC_PROCESSOR) + Processor; Pop-Survival selbsttragend |
| 084-galactic-council.md | Feature | Draft | Endgame-Influence-Voting auf Crusade-Targets / Galaxy-Boni |
| 088-combat-munition.md | Feature | Draft | BALLISTIC_AMMO/WARHEAD/PLASMA_CHARGE Verbrauch im Battle |
| 089-luxury-civilian-goods.md | Feature | Draft | Pop-Tier-Layer (Working/Middle/Upper) + Civilian-Goods |
| 090-medicine-bio-resources.md | Feature | Draft | BIOMASS/PHARMA/VACCINE/CYBERNETIC-IMPLANT |
| 091-tier3-combat-components.md | Feature | Draft | TARGETING_COMPUTER/REACTIVE_ARMOR/PLASMA_COIL/ECM/WARP_DRIVE_CORE |
| 092-rare-exotic-resources.md | Feature | Draft | XENOS_ARTIFACT/WARP_CRYSTAL/DARK_MATTER/VOID_ESSENCE/ANCIENT_DATA_CORE |
| 085-wormhole-poi.md | Feature | Done | Wormhole POI-Subtype mit Pair-Verlinkung + Galaxy-Pair-Spawn (Foundation) |
| 086-black-hole-poi.md | Feature | Draft | Schwarzes-Loch + Antimaterie-Harvest (Tech-gated) |
| 087-fog-of-war.md | Feature | Draft | Player-spezifische Discovery-Levels (UNKNOWN/METADATA/SCANNED) |
| 093-alliance-raumstation.md | Feature | Draft | Allianz-Raumstation pro System (Repair/Trade/Defense-Hub) |
| 094-build-queue.md | Feature | Done | Parallel-Slot-Model (max 3); Cancel/Refund/Hub-Bonus/Forschungs-Slots deferred |
| 094b-build-queue-cancel-refund.md | Feature | Done | CancelBuildCommand: Initial=Delete, Upgrade=Level-1; 50% Resource + 100% Pop Refund |
| 094c-build-queue-slot-extensions.md | Feature | Done | HQ-Level +1 Parallel-Slot pro Lvl-5 (Cap 8); Logistics-Forschung in T-094d split |
| 094d-build-queue-logistics-research.md | Feature | Done | logistics_1 (3 Lvl, +1 Slot/Lvl) + BuildQueueCapCalculator; HQ+Logistics stack, Hard-Cap 8 |
| 095-auto-production-routing.md | Feature | Draft | Threshold-getriggerte Auto-Trade-Routes — Folge T-110 |
| 096-player-history-stats.md | Feature | Done | Stats-Foundation: 3 Player-Counter (buildings/planets/ships) + Hooks in 3 Command-Services; Tick-/Battle-Counters in T-096b |
| 096b-stats-extension.md | Feature | Draft | Resource-Mining-Total + Battle-Counters + FactionRep-Lifetime + XP-Aggregation (Folge zu T-096) |
| 097-pop-tier-buildings.md | Feature | Draft | GENEBANK/CLONING_VAT/CIVIC_CENTER/AGRI_DOME/WATER_RECLAIMER/ATMOSPHERIC_PROCESSOR |
| 098-specialist-tracks.md | Feature | Draft | 5 Specialist-Tracks PERMANENT (+30%/-10% + Branch-Lock) |
| 099-threat-skalierung.md | Feature | Draft | PlayerScore → PvE-Encounter-Difficulty-Adaptiv |
| 100-trade-hub-buildings.md | Feature | Blocked | MARKETPLACE/SPACEPORT/CUSTOMS_HOUSE/WAREHOUSE/BAZAAR; blocked by T-177 |
| 101-planet-cap-per-player.md | Feature | Done | Planet-Cap Foundation: BASE 5 + logistics_1 Bonus, HARD_CAP 10; Colonize-Cap-Check; Demo-Status N/M |
| 101b-planet-abandon-mechanic.md | Feature | Draft | Planet-Abandon-Mechanik (Folge zu T-101) — Resources/Buildings reset + Slot frei |
| 102-ship-classes-foundation.md | Feature | Draft | 5 Combat × 3 Mk + 4 Spezial-Klassen, hohe Cost, Captain-Required |
| 103-battle-resolution-engine.md | Feature | Draft | Round-based Auto-Resolution + Tactic-Counter (RPS-light) |
| 104a-crew-foundation.md | Feature | Draft | Akademie + Officer-Quarters + Captain-Crew-Type |
| 104b-captain-skill-trees.md | Feature | Draft | Beam-Master/Missile-Spec/Shield-Tactician/Fleet-Commander |
| 104c-other-crew-roles.md | Feature | Draft | Forscher/Engineer/Diplomat (Lab-Boost/Maintenance/Reputation) |
| 105-ship-maintenance.md | Feature | Blocked | Treibstoff + Crew-Versorgung; Stranding bei Mangel; blocked by T-178 |
| 106-diplomatic-buildings.md | Feature | Draft | EMBASSY/COMM_ARRAY/CULTURAL_MISSION/INTELLIGENCE_HQ/TRANSLATOR_BUREAU |
| 107-manufacturing-buildings.md | Feature | Draft | Bündel: ~20 Manufacturing-Buildings (Munition/Civilian/Bio/Tier-3-Combat/Tier-3-Resources) |
| 108-specialty-mining-buildings.md | Feature | Draft | ASTEROID_DRONE/DEEP_DRILL/ATMOSPHERIC_HARVESTER/ICE_DRILLER/VOLCANIC_TAPPER/LUNAR_PROCESSOR |
| 109-tier3-containment-storage.md | Feature | Blocked | ANTIMATTER_CONTAINMENT/AI_CORE_VAULT/ADAMANTIUM_DEPOT/VOID_CONTAINER/ARTIFACT_VAULT; blocked by T-177 |
| 110-trade-routes.md | Feature | Draft | Auto-Transport eigene Planeten — Schiff-bound Routes |
| 111-auction-house.md | Feature | Draft | Galaxy-weit Auction + Lieferzeit via Transportschiff |
| 112-hybrid-pricing.md | Feature | Draft | Need-Based-Pricing + Statische Handelsposten (Inventory-bound, nicht-übernehmbar) |
| 113-black-market.md | Feature | Draft | Renegade-Rep-Path → illegale Tech + Tier-3-Resources |
| 114-roving-trader-ships.md | Feature | Draft | NPC-Trader-Schiffe Spawn/Despawn-Cycle (magic-spawn, kein Echtzeit-Movement) |
| 115-tier-3-resources.md | Feature | Draft | Plasteel/Adamantium/Plasma-Cell/AI-Core/Antimaterie |
| 116-mega-structures-foundation.md | Feature | Draft | Genesis-Forge first; Allianz-built; Single-Use-Terraform |
| 117-alliance-research-community-goal.md | Feature | Draft | Direkt-Donate-RP-an-Tech, kein zentraler Pool |
| 118-region-trade-arbitrage.md | Feature | Draft | Galaxy-Trade-Regionen (Core/Civilian/Border/Frontier) mit Need-Profilen + Pricing-Bias |
| 119-mega-dyson-sphere.md | Feature | Draft | Mega-Folge: Dyson-Sphäre Power-Bonus |
| 120-quest-engine-onboarding.md | Feature | Draft | Quest-Engine-Foundation + 10 Onboarding-Quests |
| 121-crusade-system.md | Feature | Draft | 6-Wochen-Cycle, World-Boss + Top-3-Title (KEIN Mandate) |
| 122-player-background.md | Feature | Done | Background-Foundation: 5-Enum + nullable Field + permanent Setter + Demo-CLI Action; Effect-Resolver in T-122b |
| 122b-background-effect-resolver.md | Feature | Draft | 7 Multiplier-Hooks (Mining/Rep/RP/Pop/Ship/Probe/Trade) + Stack-Reihenfolge mit T-098 |
| 123-player-xp-career.md | Feature | Draft | Level 1-100, asymptotischer XP-Cost, Skill-Slot-Reservation |
| 124-mega-wormhole-generator.md | Feature | Draft | Mega-Folge: Künstliches Wurmloch zwischen 2 frei-gewählten Systems |
| 125-mega-stargate.md | Feature | Draft | Mega-Folge: Stargate-Network für Multi-Endpoint-Travel |
| 126-skill-slot-implementation.md | Feature | Draft | Skill-Pick-Mechanik pro Player-Level (Folge T-123) |
| 127-tech-mining-industry-branch.md | Feature | Draft | Tech-Branch Mining/Industrie (5 Tier × 2 Nodes = 10) |
| 128-tech-shipbuilding-branch.md | Feature | Draft | Tech-Branch Schiffbau (Mark-Tier-Unlock + Klassen-Lock) |
| 129-tech-energy-branch.md | Feature | Draft | Tech-Branch Energie (Solar→Fusion→Antimatter→Zero-Point) |
| 130-alliance-treaties.md | Feature | Draft | Crusade-Coalition + Resource-Federation MVP |
| 131-spy-system-foundation.md | Feature | Draft | Spy-Network + Recon/Sabotage gegen NPC-Outposts (PvE-only) |
| 132-alliance-research-pact.md | Feature | Draft | Cross-Alliance-Research-Donation (Treaty-Folge zu T-130) |
| 133-alliance-defense-coalition.md | Feature | Draft | Joint-Defense gegen Outpost-Threats (Treaty-Folge zu T-130) |
| 134-tech-cybernetics-branch.md | Feature | Draft | Tech-Branch Kybernetik (AI-Core/Neural-Network/Cybernetic-Singularity) |
| 135-tech-diplomacy-branch.md | Feature | Draft | Tech-Branch Diplomatie (Embassy/Treaties/Pax-Imperialis) |
| 136-tech-logistics-branch.md | Feature | Draft | Tech-Branch Logistik (Bau-Queue/Trade-Routes/Planet-Cap-Skalierung) |
| 137-tech-defense-branch.md | Feature | Draft | Tech-Branch Defense (Shield/Turret/Fortress-Doctrine) |
| 138-tech-xenobiology-branch.md | Feature | Draft | Tech-Branch Xenobiologie (Xenos-Loot/Hybrid-Genetics/Genetic-Renaissance) |
| 139-tech-tree-master-design.md | Feature | Draft | Meta-Ticket: alle 10 Tech-Branches + Branch-Lock-Konsistenz |
| 140-daily-weekly-quests.md | Feature | Draft | 3 Daily + 1 Weekly Quest-Generierung adaptiv |
| 141-achievement-system.md | Feature | Draft | 100+ Achievements, NUR Cosmetic (Title/Banner) |
| 142-login-streak.md | Feature | Draft | 7-Tage-Cycle Login-Belohnung, Cycle wiederholt |
| 143-cosmetics-inventory-system.md | Feature | Draft | Title/Banner/Frame/Schiff-Skin/Building-Skin Equip-Slots |
| 150-bubble-protection.md | Feature | Done | Bubble-Foundation: PlayerBubbleStatus + Auto-Exit nach 2. Planet-Claim; Effekte+CatchUp in T-150b |
| 150b-bubble-effects-and-catchup.md | Feature | Draft | Bubble-Skip-Effekte in T-074/T-075/T-111/T-160 + Catch-Up-Mining-Multiplier 14d ×1.5 |
| 151-soft-cap-diminishing-returns.md | Feature | Done | SoftCapConfig (Pop/Building-Lvl/Stockpile) + 3 Hook-Stellen |
| 152-inactivity-protection.md | Feature | Draft | 7d/30d/90d-Stages + Vacation-Welcome-Pack |
| 153-alliance-rescue.md | Feature | Draft | Stranded-Rescue + Defense-Reinforcement zwischen Allianz-Members |
| 160-galaxy-map.md | Feature | Draft | Interaktive Map (SVG-MVP, Stimulus-controlled, Fog-of-War) |
| 161-notifications.md | Feature | Draft | In-Game-Bell + optional PWA-Web-Push |
| 162-build-templates.md | Feature | Draft | Build-Templates speichern/applizieren auf neue Planeten |
| 163-strategy-forecast-dashboard.md | Feature | Draft | Resource-Forecast/Build-ETAs/ROI-Calculator/Threat-Indicator |
| 164-battle-replay-ui.md | Feature | Draft | Battle-Replay (Table-MVP, Animated-Folge) |
| 165-settings-personalization.md | Feature | Draft | UI-Theme/Default-Tactic/Galaxy-Filter/Notification-Prefs |
| 166-animated-battle-replay.md | Feature | Draft | Animated-Battle-Replay-View (Folge zu T-164) |
| 167-cleanup-loose-ends.md | TechDebt | Done | Status-Sync, Stale-Stubs raus, 6 Folge-Drafts (T-026b/c, T-064b, T-094b/c, T-015c), T-024 superseded |
| 168-demo-cli-env-preflight.md | Bug | Done | `app:demo:run` Auto-Re-Exec in demo-Env via Symfony\Process — kein --env=demo mehr nötig |
| 169-demo-reset-action-bug.md | Bug | Done | Reset-Action zentralisiert via bootstrapFreshPlayer + pendingPlayerSwap; Buff+Galaxy konsistent, Loop schwenkt um |
| 171-building-uniqueness-slot-concept.md | Feature | Done | 6 Strategic-Unique + Slot-Size pro Building (1-3) + Slot-Cap pro PlanetSize (8-40) |
| 172-hq-vs-hub-refactor.md | Feature | Done | HQ (unique, slot-3, +25 Pop, Storage, Slot-Bonus capped); HUB (multi, slot-1, +100 Pop); CONSTRUCTION_YARD rename |
| 173-hq-building-level-cap.md | Feature | Draft | HQ-Level cappt Building-Level + steile HQ-Cost-Curve + lokaler Build-Speed-Bonus |
| 174-deprecate-station-build.md | TechDebt | Done | Station-Build soft-deprecated: wirft `StationConstructionDeprecatedException`; 6 orphan POI-Exceptions entfernt; Hard-Remove erst nach T-175 |
| 175-pirate-station-spawn.md | Feature | Draft | Galaxy-Bootstrap-Spawn von Pirate-owned + ABANDONED Stations (Lost-Tech-Lore) |
| 176-pirate-station-takeover.md | Feature | Draft | Pirate-Takeover ABANDONED → Pirate + Combat-Capture-Mechanik (zweistufig) |
| 177-generic-storage-refactor.md | Feature | Done | Generic Volume-Storage Planet (m³) + WAREHOUSE konsolidiert 6 T-061-Buildings; Volume-Cap-Stop für Production; canAddItem/maxAddableQuantity. T-061 superseded |
| 178-ship-cargo-universal.md | Feature | Blocked | Ship-Cargo-Universal: alle Schiffe haben Cargo-Volume; blocked by T-177 |
| 179-pop-as-storage-item.md | Feature | Blocked | Pop wird Storage-Item mit hohem Size-Multi; T-004/T-005 refactored; blocked by T-178 |
| 180-resource-volume-config.md | Feature | Done | ResourceVolumeConfig (m³/Unit für 14 ResourceTypes + Pop=10); Foundation für T-177ff Generic-Storage |
| 181-resource-volume-debug-command.md | Feature | Done | `app:debug:resource-volume` Symfony-Command — Volume-Tabelle sortiert + Beispiel-Profile |
| 182-techdebt-remove-university-building.md | TechDebt | Done | UNIVERSITY-Building entfernt (Wort-Mix-Up mit RESEARCH_LAB); T-070-AC + T-070b-Section + T-089/T-097a Refs aufgeräumt |
| 183-station-generic-storage.md | Feature | Blocked | Station-Generic-Volume-Storage (analog T-177 Planet); blocked by T-177 + T-180 |
| 170-tech-tree-building-research-gating.md | Feature | Done | Tech-Tree Foundation: Buildings via Research locked, Research via Building-Vorhandensein gated; 6 Tier-1-Nodes, 13 Buildings gated |
