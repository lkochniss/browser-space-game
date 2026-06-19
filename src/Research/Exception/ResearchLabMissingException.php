<?php

declare(strict_types=1);

namespace App\Research\Exception;

use DomainException;

class ResearchLabMissingException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Forschung benötigt mindestens ein fertiges RESEARCH_LAB-Building auf einem Planeten.');
    }
}
