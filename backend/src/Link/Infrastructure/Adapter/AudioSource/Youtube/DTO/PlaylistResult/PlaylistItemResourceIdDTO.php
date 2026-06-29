<?php

declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO\PlaylistResult;

final readonly class PlaylistItemResourceIdDTO
{
    private function __construct(
        public string $kind,
        public string $videoId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: $data['kind'],
            videoId: $data['videoId'],
        );
    }
}
