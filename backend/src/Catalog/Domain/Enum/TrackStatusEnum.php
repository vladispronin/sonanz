<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enum;

enum TrackStatusEnum: string
{
    case Created = 'created';
    case Downloaded = 'downloaded';
    case Tagged = 'tagged';
    case Completed = 'completed';
    case Failed = 'failed';
}
