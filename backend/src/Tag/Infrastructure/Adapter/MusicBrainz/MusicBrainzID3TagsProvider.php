<?php

declare(strict_types=1);

namespace App\Tag\Infrastructure\Adapter\MusicBrainz;

use App\Tag\Domain\Port\ID3TagsProviderInterface;
use App\Tag\Domain\ValueObject\AudioTagQuery;
use App\Tag\Domain\ValueObject\TrackMetadata;
use App\Tag\Infrastructure\Adapter\MusicBrainz\DTO\MusicBrainzMetadataDTO;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MusicBrainzID3TagsProvider implements ID3TagsProviderInterface
{
    private const API_URL = 'https://musicbrainz.org/ws/2/recording/';

    public function __construct(
        private HttpClientInterface $httpClient,
    ) {}

    public function getTags(AudioTagQuery $query): ?TrackMetadata
    {
        $response = $this->httpClient->request('GET', self::API_URL, [
            'query' => [
                'query' => 'artist:' . $query->artist . '+recording:' . $query->title,
                'fmt' => 'json',
                'limit' => 1,
            ],
        ]);

        $dto = MusicBrainzMetadataDTO::fromArray($response->toArray());
        if (!$dto) {
            return null;
        }

        return new TrackMetadata(
            title: $dto->title,
            artist: $dto->artist,
            albumArtist: $dto->albumArtist,
            album: $dto->album,
            trackNumber: $dto->trackNumber,
            releaseGroupId: $dto->releaseGroupId,
        );
    }
}
