<?php

declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO\SearchResult;

final readonly class SearchResultIdDTO
{
    private function __construct(
        public string $kind,
        public ?string $videoId,
        public ?string $playlistId,
        public ?string $channelId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: $data['kind'],
            videoId: $data['videoId'] ?? null,
            playlistId: $data['playlistId'] ?? null,
            channelId: $data['channelId'] ?? null,
        );
    }
}
