# Tickets

| File | Type | Status | Summary |
|------|------|--------|---------|
| 001-renewable-resources.md | Feature | Done | WATER/FOOD/OXYGEN als ResourceType; Start-Amount 100; Base-Werte gesetzt |
| 002-finite-resources-extend.md | Feature | Done | 6 Erze + 6 Mines + Base-Werte gestaffelt; canProduce-Bug gefixt |
| 003-erzeugnis-eisenbarren.md | Feature | Done | ResourceCategory + IRON_BAR + IRON_SMELTER + Refinement-Tick (2:1:1) |
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
| 015b-station-cargo-transfer.md | Feature | Draft | Station als LoadCargo/UnloadCargo-Target (T-015 Erweiterung für T-023b) |
| 016-bergungsschiff.md | Feature | Done | ShipType::SALVAGE + Echtzeit-Salvage (50 Units/min) für AsteroidField + Field-Cleanup |
| 017-flotte-movement.md | Feature | Done | Persistent-Fleet + Wallclock-Movement (Slowest-Ship-Speed) + FleetArrivalService; Magic-Dock-Cleanup |
| 017b-fleet-movement-modifiers.md | Feature | Draft | Nebel-Detection-Hook + Wormhole-Travel-Reduktion/Cooldown/Treibstoff (Folge-Modifier zu T-017) |
| 018-teleskop-discovery.md | Feature | Open | Teleskop-Building + Meta-Erkundung von Systemen |
| 019-poi-system.md | Feature | Done | POI-Foundation (STI) + 7 PoiTypes + SolarSystem.pois |
| 020-asteroidenfeld.md | Feature | Done | AsteroidField POI-Subtype (STI) + Galaxy-Spawn 0-2 pro System mit FINITE-Erzen |
| 021-truemmerfeld-recycling.md | Feature | Open | Trümmerfeld + Trümmer + Recycling-Anlage Chain |
| 022-nebel-poi.md | Feature | Done | Nebula POI-Subtype (STI) + concealmentLevel + 30%-Galaxy-Spawn |
| 023-raumstation.md | Feature | Done | SpaceStation POI (max 1/System, Shipyard-L3-Gate, Storage 100k); Maintenance/Übernahme = T-023b |
| 023b-station-maintenance-takeover.md | Feature | Draft | Station-Maintenance-Tick + Pop-Mortality + ABANDONED-State + ClaimAbandonedStationCommand |
| 024-raumschlacht.md | Feature | Open | Legacy-Anker, abgelöst durch T-103 (PvE-Pivot) |
| 025-forschung-framework.md | Feature | Open | Forschungs-Framework (Tree, Levels) |
| 026-antriebstechnologie-tree.md | Feature | Open | Antriebs-Tree (4 Standard + 3 FTL) |
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
| 064-construction-speed-boost.md | Feature | Open | Forschung + Buildings reduzieren Bauzeit — Folge T-062 |
| 065-energy-system.md | Feature | Draft | Power-Net pro Planet — Hub-Reaktor + Power-Plants vs Consumer |
| 066-treibstoff-resource.md | Feature | Draft | H2 + Promethium als FUEL-Resources für Schiffe |
| 067-erzeugnis-tree-erweiterung.md | Feature | Draft | Steel/Chip/Composite/Hull/Shield Tier-2-Outputs |
| 068-defense-buildings.md | Feature | Draft | Shield/Turret/Sensor/AA für Planet-Defense |
| 069-research-lab-tier.md | Feature | Draft | Lab-Tier mit RP-Output + Tech-Tier-Lock |
| 070-pop-qol-buildings.md | Feature | Draft | Hospital/University/Cultural-Center/Temple |
| 071-power-plants.md | Feature | Draft | Solar/Fusion/Antimaterie-Reactor (3 Tiers) |
| 072-erzeugnis-production-buildings.md | Feature | Draft | Steel-Smelter/Chip-Fab/Composite-Plant/Hull-Foundry/Shield-Assembler |
| 073-npc-faction-foundation.md | Feature | Done | Faction-Domain + lazy Reputation + 4 Seeds (Pirate/Renegade/Xenos/MerchantGuild); Migration + 4 Test-Suiten |
| 074-pirate-encounter-spawn.md | Feature | Draft | Pirat-Random-Encounters in Systemen, Threat-skaliert |
| 075-renegade-xenos-outposts.md | Feature | Draft | Statische POI-Threats (Renegade-Stronghold/Xenos-Hive/Wormhole-Gate) |
| 076-galaxy-events.md | Feature | Draft | Limited-Time-Galaxy-Events (Warp-Storm, Anomalie, Invasion, Friedensfest) |
| 077-world-boss-raid-target.md | Feature | Draft | Allianz-PvE-Bosse mit Multi-Player-Damage-Tracking |
| 078-faction-quest-storylines.md | Feature | Draft | Faction-Story-Quests mit Reputation-Gates (separate von Onboarding/Daily) |
| 079-spy-heist-infiltration.md | Feature | Draft | Spy-Erweiterung — Heist + Long-Term-Infiltration (PvE-only) |
| 080-loot-system.md | Feature | Draft | Drop-Tabellen pro Faction, Resource/Tech-Fragment/Blueprint/Cosmetic |
| 081-heimat-schutz-anti-crush.md | Feature | Draft | Immortal-HomePlanet + Pop-Loss-Cap + Vault + Schild-Cooldown |
| 082-interactive-demo-cli.md | Feature | Done | Sandbox-CLI `app:demo:run` mit Choice-Menü für alle Game-Actions + Time-Travel |
| 082b-demo-cli-ux-polish.md | Feature | Done | Galaxy-Overview + Cost-Preview + Demo-Buff (Hub L1, 300 W/F/O) + Wormhole-Garantie |
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
| 094-build-queue.md | Feature | Draft | Bau-Queue 3+ Slots pro Planet, Forschung erweitert |
| 095-auto-production-routing.md | Feature | Draft | Threshold-getriggerte Auto-Trade-Routes — Folge T-110 |
| 096-player-history-stats.md | Feature | Draft | Persistent Player-Stats (Battles/Resources/Buildings) |
| 097-pop-tier-buildings.md | Feature | Draft | GENEBANK/CLONING_VAT/CIVIC_CENTER/AGRI_DOME/WATER_RECLAIMER/ATMOSPHERIC_PROCESSOR |
| 098-specialist-tracks.md | Feature | Draft | 5 Specialist-Tracks PERMANENT (+30%/-10% + Branch-Lock) |
| 099-threat-skalierung.md | Feature | Draft | PlayerScore → PvE-Encounter-Difficulty-Adaptiv |
| 100-trade-hub-buildings.md | Feature | Draft | MARKETPLACE/SPACEPORT/CUSTOMS_HOUSE/WAREHOUSE/BAZAAR |
| 101-planet-cap-per-player.md | Feature | Draft | Max 5 Planeten/Player (erweiterbar via Forschung bis 10) |
| 102-ship-classes-foundation.md | Feature | Draft | 5 Combat × 3 Mk + 4 Spezial-Klassen, hohe Cost, Captain-Required |
| 103-battle-resolution-engine.md | Feature | Draft | Round-based Auto-Resolution + Tactic-Counter (RPS-light) |
| 104a-crew-foundation.md | Feature | Draft | Akademie + Officer-Quarters + Captain-Crew-Type |
| 104b-captain-skill-trees.md | Feature | Draft | Beam-Master/Missile-Spec/Shield-Tactician/Fleet-Commander |
| 104c-other-crew-roles.md | Feature | Draft | Forscher/Engineer/Diplomat (Lab-Boost/Maintenance/Reputation) |
| 105-ship-maintenance.md | Feature | Draft | Treibstoff + Crew-Versorgung; Stranding bei Mangel; KEIN Hull-Wear |
| 106-diplomatic-buildings.md | Feature | Draft | EMBASSY/COMM_ARRAY/CULTURAL_MISSION/INTELLIGENCE_HQ/TRANSLATOR_BUREAU |
| 107-manufacturing-buildings.md | Feature | Draft | Bündel: ~20 Manufacturing-Buildings (Munition/Civilian/Bio/Tier-3-Combat/Tier-3-Resources) |
| 108-specialty-mining-buildings.md | Feature | Draft | ASTEROID_DRONE/DEEP_DRILL/ATMOSPHERIC_HARVESTER/ICE_DRILLER/VOLCANIC_TAPPER/LUNAR_PROCESSOR |
| 109-tier3-containment-storage.md | Feature | Draft | ANTIMATTER_CONTAINMENT/AI_CORE_VAULT/ADAMANTIUM_DEPOT/VOID_CONTAINER/ARTIFACT_VAULT |
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
| 122-player-background.md | Feature | Draft | 5 Backgrounds permanent (+5%/-2% Identity-Bonus) |
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
| 150-bubble-protection.md | Feature | Draft | Bubble bis 2. Planet — keine NPC, kein Auction; Catch-Up +50% Mining 14d |
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
