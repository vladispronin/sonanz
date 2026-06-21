<?php

declare(strict_types=1);

namespace App\Shared\Application\Message;

use App\Shared\Domain\ValueObject\TrackArchiveEntry;

final readonly class ArchiveAlbumMessage
{
    /**
     * @param TrackArchiveEntry[] $tracks
     */
    public function __construct(
        public string $artist,
        public string $albumTitle,
        public array $tracks
    ) {}
}
