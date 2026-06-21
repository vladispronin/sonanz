<?php

declare(strict_types=1);

namespace App\Tag\Infrastructure\Adapter\AcoustId;

use App\Tag\Domain\Port\ID3TagsProviderInterface;
use App\Tag\Domain\ValueObject\AudioTagQuery;
use App\Tag\Domain\ValueObject\TrackMetadata;
use App\Tag\Infrastructure\Adapter\AcoustId\DTO\AcoustIdMetadataDTO;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AcoustIdID3TagsProvider implements ID3TagsProviderInterface
{
    private const API_URL = 'https://api.acoustid.org/v2/lookup';
    private const META = 'recordings+releasegroups+releases+usermeta+tracks';

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey,
    ) {}

    public function getTags(AudioTagQuery $query): ?TrackMetadata
    {
        $response = $this->httpClient->request('GET', self::API_URL, [
            'query' => [
                'client' => $this->apiKey,
                'meta' => self::META,
                'duration' => $query->fingerprint->duration,
                'fingerprint' => $query->fingerprint->fingerprint,
            ],
        ]);

        $dto = AcoustIdMetadataDTO::fromArray($response->toArray());
        if (!$dto) {
            return null;
        }

        return new TrackMetadata(
            title: $dto->title,
            artist: $dto->artist,
            album: $dto->album,
            trackNumber: $dto->trackNumber,
            releaseGroupId: $dto->releaseGroupId,
        );
    }
}
