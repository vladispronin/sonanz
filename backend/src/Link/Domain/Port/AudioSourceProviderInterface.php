<?php

declare(strict_types=1);

namespace App\Link\Domain\Port;

use App\Link\Domain\ValueObject\AudioSourceLink;
use App\Link\Domain\ValueObject\LinkSearchQuery;

interface AudioSourceProviderInterface
{
    /**
     * @return AudioSourceLink[]
     */
    public function search(LinkSearchQuery $query): array;
}
