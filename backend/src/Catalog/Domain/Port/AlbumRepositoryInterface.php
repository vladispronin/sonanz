<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Port;

use App\Catalog\Domain\Entity\Album;

interface AlbumRepositoryInterface
{
    public function create(string $jobId): Album;
}
