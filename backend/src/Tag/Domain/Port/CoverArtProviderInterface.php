<?php

declare(strict_types=1);

namespace App\Tag\Domain\Port;

interface CoverArtProviderInterface
{
    public function getByReleaseGroupId(?string $releaseGroupId): ?string;
}
