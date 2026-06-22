<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Port;

use App\Catalog\Domain\Entity\Album;
use App\Catalog\Domain\Entity\Track;
use App\Shared\Domain\ValueObject\TrackArchiveEntry;
use Symfony\Component\Uid\Uuid;

interface TrackRepositoryInterface
{
    public function create(Uuid $jobId, string $url, string $title, ?Album $album): Track;

    public function findById(Uuid $trackId): Track;

    public function markAsDownloaded(Uuid $trackId): void;

    public function markAsTagged(Uuid $trackId): void;

    public function markAsCompleted(Uuid $trackId): void;

    public function markAsFailed(Uuid $trackId): void;

    /**
     * @return TrackArchiveEntry[]
     */
    public function getAlbumTracksData(Uuid $albumId): array;

    public function allTracksCompleted(Uuid $albumId): bool;

    public function updateTitle(Uuid $trackId, string $title): void;

    public function enrichWithMetadataAuthor(Uuid $trackId, string $author): void;
}
