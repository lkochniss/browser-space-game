<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Type;

use App\Research\ValueObject\ResearchId;

class ResearchIdType extends AbstractUuidType
{
    public const NAME = 'research_id';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getValueObjectClass(): string
    {
        return ResearchId::class;
    }
}
