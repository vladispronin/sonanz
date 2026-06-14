<?php

declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO\PlaylistResult;

final readonly class PlaylistItemContentDetailsDTO
{
    private function __construct(
        public string $videoId,
        public string $videoPublishedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            videoId: $data['videoId'],
            videoPublishedAt: $data['videoPublishedAt'],
        );
    }
}
