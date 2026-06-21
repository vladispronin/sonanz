<?php

declare(strict_types=1);

namespace App\Tag\Infrastructure\Adapter\AcoustId\DTO;

final readonly class AcoustIdMetadataDTO
{
    private function __construct(
        public ?string $title,
        public ?string $artist,
        public ?string $album,
        public ?int $trackNumber,
        public ?string $releaseGroupId,
        public float $score,
    ) {}

    public static function fromArray(array $data): ?self
    {
        $result = $data['results'][0] ?? null;
        if (!$result) {
            return null;
        }

        $recording = $result['recordings'][0] ?? [];
        $releaseGroup = $recording['releasegroups'][0] ?? [];
        $medium = $releaseGroup['releases'][0]['mediums'][0] ?? [];
        $track = $medium['tracks'][0] ?? [];

        return new self(
            title: $recording['title'] ?? null,
            artist: $recording['artists'][0] ?? null,
            album: $releaseGroup['title'] ?? null,
            trackNumber: isset($track['position']) ? (int) $track['position'] : null,
            releaseGroupId: $releaseGroup['id'] ?? null,
            score: (float) ($result['score'] ?? 0),
        );
    }
}
