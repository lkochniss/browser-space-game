<?php

declare(strict_types=1);

namespace App\Ship\Exception;

use App\Ship\ValueObject\ShipClass;

final class MissingCaptainException extends \DomainException
{
    public function __construct(ShipClass $class)
    {
        parent::__construct(sprintf(
            'Building %s requires an idle Captain (T-104a). No idle Captain available for player.',
            $class->value,
        ));
    }
}
