<?php

declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\AudioSource\Youtube;

use App\Link\Domain\Enum\TitleTypeEnum;
use App\Link\Domain\Port\AudioSourceProviderInterface;
use App\Link\Domain\ValueObject\AudioSourceLink;
use App\Link\Domain\ValueObject\LinkSearchQuery;
use App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO\PlaylistResult\ApiPlaylistResultDTO;
use App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO\SearchResult\ApiSearchResultDTO;
use App\Link\Infrastructure\Adapter\AudioSource\Youtube\Enum\SearchObjectIdTypeEnum;
use App\Link\Infrastructure\Adapter\AudioSource\Youtube\Enum\SearchObjectTypeEnum;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class YoutubeAudioSourceProvider implements AudioSourceProviderInterface
{
    private const string YOUTUBE_GOOGLEAPIS_DOMAIN = 'https://youtube.googleapis.com';
    private const string WWW_GOOGLEAPIS_DOMAIN = 'https://www.googleapis.com';
    private const string SEARCH_ENDPOINT = '/youtube/v3/search';
    private const string PLAYLIST_ITEMS_ENDPOINT = '/youtube/v3/playlistItems';
    private const string VIDEO_LINK_PREFIX = 'https://www.youtube.com/watch?v=';
    private const string SEARCH_PART_VALUE = 'snippet';
    private const string PLAYLIST_PART_VALUE = 'contentDetails';
    private const int SEARCH_MAX_RESULTS = 1;
    private const int PLAYLIST_TRACKS_MAX_RESULTS = 50;
    private HttpClientInterface $httpClient;

    private function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return AudioSourceLink[]
     */
    public function search(LinkSearchQuery $query): array
    {
        $searchObjectType = SearchObjectTypeEnum::fromTitleType($query->titleType);

        $searchData = $this->get(
            self::YOUTUBE_GOOGLEAPIS_DOMAIN . self::SEARCH_ENDPOINT,
            [
                'part' => self::SEARCH_PART_VALUE,
                'type' => $searchObjectType,
                'maxResults' => self::SEARCH_MAX_RESULTS,
                'q' => $query->author . ' - ' . $query->title,
                'key' => env('YOUTUBE_API_KEY'),
            ]
        );

        if (!$searchData) {
            return [];
        }

        $searchObjectIdType = SearchObjectIdTypeEnum::fromTitleType($query->titleType);

        $searchResultDTO = ApiSearchResultDTO::fromArray($searchData);

        $mediaId = $searchResultDTO->getMediaId($searchObjectIdType);

        if ($query->titleType === TitleTypeEnum::Track) {
            return [
                new AudioSourceLink(self::VIDEO_LINK_PREFIX . $mediaId)
            ];
        }

        $playlistData = $this->get(
            self::WWW_GOOGLEAPIS_DOMAIN . self::PLAYLIST_ITEMS_ENDPOINT,
            [
                'part' => self::PLAYLIST_PART_VALUE,
                'playlistId' => $mediaId,
                'maxResults' => self::PLAYLIST_TRACKS_MAX_RESULTS,
                'key' => env('YOUTUBE_API_KEY'),
            ]
        );

        if (!$playlistData) {
            return [];
        }

        $linksList = [];

        $playlistResultDTO = ApiPlaylistResultDTO::fromArray($playlistData);

        $playlistItems = $playlistResultDTO->getPlaylistItems();

        foreach ($playlistItems as $playlistItem) {
            $linksList[] = new AudioSourceLink(
                $playlistItem->getVideoId()
            );
        }

        return $linksList;
    }

    private function get(string $url, array $options): ?array
    {
        try {
            $result = $this->httpClient->request(
                'GET',
                $url,
                $options
            );

            $content = $result->getContent();

            return json_decode($content, true);
        } catch (Throwable $e) {
            //TODO logging exception

            return null;
        }
    }
}
