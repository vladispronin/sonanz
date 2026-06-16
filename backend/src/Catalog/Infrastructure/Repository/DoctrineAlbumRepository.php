<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Repository;

use App\Catalog\Domain\Entity\Album;
use App\Catalog\Domain\Port\AlbumRepositoryInterface;

class DoctrineAlbumRepository implements AlbumRepositoryInterface
{

    public function create(string $jobId): Album
    {
        return new Album(jobId: $jobId);
    }
}
