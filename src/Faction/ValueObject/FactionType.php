<?php

declare(strict_types=1);

namespace App\Faction\ValueObject;

enum FactionType: string
{
    case PIRATE = 'pirate';
    case RENEGADE = 'renegade';
    case XENOS = 'xenos';
    case MERCHANT_GUILD = 'merchant_guild';
}
