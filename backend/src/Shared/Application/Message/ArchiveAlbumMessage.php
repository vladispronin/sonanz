<?php

declare(strict_types=1);

namespace App\Shared\Application\Message;

use App\Shared\Domain\ValueObject\TrackArchiveEntry;
use Symfony\Component\Uid\Uuid;

final readonly class ArchiveAlbumMessage
{
    /**
     * @param TrackArchiveEntry[] $tracks
     */
    public function __construct(
        public Uuid $albumId,
        public array $tracks
    ) {}
}
