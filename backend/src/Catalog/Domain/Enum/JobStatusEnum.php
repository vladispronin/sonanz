<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enum;

enum JobStatusEnum: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Failed = 'failed';
}
