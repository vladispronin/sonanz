<?php

declare(strict_types=1);

namespace App\Tag\Infrastructure\Adapter\CoverArtArchive;

use App\Tag\Domain\Port\CoverArtProviderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CoverArtArchiveProvider implements CoverArtProviderInterface
{
    private const API_URL = 'https://coverartarchive.org/release-group/';

    public function __construct(
        private HttpClientInterface $httpClient,
        private ?string $proxy = null,
    ) {}

    public function getByReleaseGroupId(?string $releaseGroupId): ?string
    {
        if (is_null($releaseGroupId)) {
            return null;
        }

        $response = $this->httpClient->request(
            'GET',
            self::API_URL . $releaseGroupId . '/front',
            $this->proxy ? ['proxy' => $this->proxy] : [],
        );

        if (in_array($response->getStatusCode(), [404, 500])) {
            return null;
        }

        return $response->getContent();
    }
}
