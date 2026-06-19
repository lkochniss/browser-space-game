<?php

declare(strict_types=1);

namespace App\Faction\Service;

use App\Faction\Model\Faction;
use App\Faction\Repository\FactionRepository;
use App\Faction\ValueObject\FactionId;
use App\Faction\ValueObject\FactionType;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Idempotent: legt die 4 Default-Factions an, wenn sie nicht existieren (lookup via slug).
 *
 * Aufruf-Punkte:
 * - In IntegrationTestCase nach Schema-Create
 * - Per Symfony-Console-Command beim Deployment (T-073-folge: app:faction:seed)
 */
readonly class FactionSeedService
{
    public function __construct(
        private EntityManagerInterface $em,
        private FactionRepository $factionRepo,
    ) {
    }

    public function seed(): void
    {
        foreach ($this->factionDefinitions() as $def) {
            if ($this->factionRepo->findBySlug($def['slug']) !== null) {
                continue;
            }

            $faction = new Faction(
                id: FactionId::generate(),
                slug: $def['slug'],
                name: $def['name'],
                type: $def['type'],
                isAlwaysHostile: $def['isAlwaysHostile'],
                defaultReputation: $def['defaultReputation'],
                description: $def['description'],
            );

            $this->em->persist($faction);
        }

        $this->em->flush();
    }

    /**
     * @return array<array{slug:string,name:string,type:FactionType,isAlwaysHostile:bool,defaultReputation:int,description:string}>
     */
    private function factionDefinitions(): array
    {
        return [
            [
                'slug' => 'merchant_guild',
                'name' => 'Galaktische Händler-Gilde',
                'type' => FactionType::MERCHANT_GUILD,
                'isAlwaysHostile' => false,
                'defaultReputation' => 0,
                'description' => 'Neutrale Handels-Allianz. Reputation aufbaubar via Transport- und Handelsmissionen.',
            ],
            [
                'slug' => 'pirate_consortium',
                'name' => 'Pirat-Konsortium',
                'type' => FactionType::PIRATE,
                'isAlwaysHostile' => true,
                'defaultReputation' => -100,
                'description' => 'Gesetzlose Banden in den Randsystemen. Easy-Tier Loot-Source.',
            ],
            [
                'slug' => 'renegade_warbands',
                'name' => 'Abtrünnige',
                'type' => FactionType::RENEGADE,
                'isAlwaysHostile' => true,
                'defaultReputation' => -100,
                'description' => 'Ehemalige Imperiale, die sich abgewandt haben. Mid-Tier, droppt Imperial-Tech.',
            ],
            [
                'slug' => 'xenos_splinter',
                'name' => 'Xenos-Splitter',
                'type' => FactionType::XENOS,
                'isAlwaysHostile' => true,
                'defaultReputation' => -100,
                'description' => 'Versprengte fremde Lebensformen. High-Tier, exotische Tech.',
            ],
        ];
    }
}
