<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

final readonly class TrackTagMetadata
{
    public function __construct(
        public ?string $trackTitle,
        public ?string $albumTitle,
        public ?string $artist,
    ) {}
}
