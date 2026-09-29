<?php

declare(strict_types=1);

namespace App\Tag\Infrastructure\Adapter\AcoustId\DTO;

final readonly class AcoustIdMetadataDTO
{
    private function __construct(
        public ?string $title,
        public ?string $artist,
        public ?string $albumArtist,
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
            artist: array_key_exists('artists', $recording) && !is_null($recording['artists']) ?
                self::getArtistsAsString($recording['artists']) : null,
            albumArtist: array_key_exists('artists', $releaseGroup) && !is_null($releaseGroup['artists']) ?
                self::getArtistsAsString($releaseGroup['artists']) : null,
            album: $releaseGroup['title'] ?? null,
            trackNumber: isset($track['position']) ? (int) $track['position'] : null,
            releaseGroupId: $releaseGroup['id'] ?? null,
            score: (float) ($result['score'] ?? 0),
        );
    }

    private static function getArtistsAsString(array $artists): string
    {
        $result = '';

        foreach ($artists as $artist) {
            if (isset($artist['joinphrase'])) {
                $result .= self::extractArtistName($artist) . $artist['joinphrase'];
            } else {
                $result .= self::extractArtistName($artist);
            }
        }

        return $result;
    }

    private static function extractArtistName(mixed $artist): string
    {
        if (is_array($artist)) {
            return $artist['name'];
        } else {
            return $artist;
        }
    }
}
