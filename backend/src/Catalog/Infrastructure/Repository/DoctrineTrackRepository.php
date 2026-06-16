<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Repository;

use App\Catalog\Domain\Entity\Album;
use App\Catalog\Domain\Entity\Track;
use App\Catalog\Domain\Port\TrackRepositoryInterface;

class DoctrineTrackRepository implements TrackRepositoryInterface
{

    public function create(string $jobId, string $url, ?Album $album): Track
    {
        return new Track(
            jobId: $jobId,
            url: $url,
            album: $album
        );
    }
}
