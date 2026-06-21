<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Port;

use App\Catalog\Domain\Entity\Album;
use Symfony\Component\Uid\Uuid;

interface AlbumRepositoryInterface
{
    public function create(Uuid $jobId, string $title): Album;

    public function getTitleById(Uuid $albumId): string;

    public function updateTitle(Uuid $albumId, string $title): void;
}
