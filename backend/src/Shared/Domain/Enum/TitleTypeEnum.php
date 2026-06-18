<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enum;

enum TitleTypeEnum: string
{
    case Track = 'track';
    case Album = 'album';
}
