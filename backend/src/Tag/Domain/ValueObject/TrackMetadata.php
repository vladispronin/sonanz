<?php

declare(strict_types=1);

namespace App\Tag\Domain\ValueObject;

final readonly class TrackMetadata
{
    public function __construct(
        public ?string $title,
        public ?string $artist,
        public ?string $albumArtist,
        public ?string $album,
        public ?int $trackNumber,
        public ?string $releaseGroupId,
    ) {}

    public function isEmpty(): bool
    {
        return $this->title === null
            && $this->artist === null
            && $this->albumArtist === null
            && $this->releaseGroupId === null;
    }
}
