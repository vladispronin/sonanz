<?php

declare(strict_types=1);

namespace App\Link\Domain\Port;

use App\Link\Domain\ValueObject\AudioSearchQuery;
use App\Shared\Domain\ValueObject\AudioSourceLink;

interface AudioSourceProviderInterface
{
    /**
     * @return AudioSourceLink[]
     */
    public function search(AudioSearchQuery $query): array;
}
