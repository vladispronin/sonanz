<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Port;

use App\Catalog\Domain\Entity\Album;
use App\Catalog\Domain\Entity\Track;

interface TrackRepositoryInterface
{
    public function create(string $jobId, string $url, ?Album $album): Track;
}
