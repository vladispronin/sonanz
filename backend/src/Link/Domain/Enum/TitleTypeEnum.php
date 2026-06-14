<?php
declare(strict_types=1);

namespace App\Link\Domain\Enum;

enum TitleTypeEnum: string
{
    case Track = 'track';
    case Album = 'album';
}
