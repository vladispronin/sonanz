<?php

declare(strict_types=1);

namespace App\Tag\Infrastructure\Adapter\MusicBrainz\DTO;

final readonly class MusicBrainzMetadataDTO
{
    private function __construct(
        public ?string $title,
        public ?string $artist,
        public ?string $albumArtist,
        public ?string $album,
        public ?int $trackNumber,
        public ?string $releaseGroupId,
    ) {}

    public static function fromArray(array $data): ?self
    {
        $recording = $data['recordings'][0] ?? null;
        if (!$recording) {
            return null;
        }

        $release = $recording['releases'][0] ?? [];
        $releaseGroup = $release['release-group'] ?? [];
        $track = $release['media'][0]['track'][0] ?? [];

        return new self(
            title: $recording['title'] ?? null,
            artist: self::getArtistsAsString($recording['artist-credit'] ?? []),
            albumArtist: self::getArtistsAsString(
                $release['artist-credit'] ?? $recording['artist-credit'] ?? []
            ),
            album: $releaseGroup['title'] ?? null,
            trackNumber: isset($track['number']) ? (int) $track['number'] : null,
            releaseGroupId: $releaseGroup['id'] ?? null,
        );
    }

    private static function getArtistsAsString(array $artists): string
    {
        $result = '';

        foreach ($artists as $artist) {
            if (isset($artist['joinphrase'])) {
                $result .= $artist['name'] . $artist['joinphrase'];
            } else {
                $result .= $artist['name'];
            }
        }

        return $result;
    }
}
