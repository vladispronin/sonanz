<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Port;

use App\Catalog\Domain\Entity\Album;
use App\Catalog\Domain\Entity\Track;
use Symfony\Component\Uid\Uuid;

interface TrackRepositoryInterface
{
    public function create(Uuid $jobId, string $url, ?Album $album): Track;
}
