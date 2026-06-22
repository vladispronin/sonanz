<?php

declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\AudioSource\Youtube;

use App\Link\Domain\Port\AudioSourceProviderInterface;
use App\Link\Domain\ValueObject\AudioSearchQuery;
use App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO\PlaylistResult\ApiPlaylistResultDTO;
use App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO\SearchResult\ApiSearchResultDTO;
use App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO\SearchResult\SearchResultItemDTO;
use App\Link\Infrastructure\Adapter\AudioSource\Youtube\Enum\SearchObjectTypeEnum;
use App\Link\Infrastructure\Adapter\AudioSource\Youtube\Service\TitleRelevanceScorer;
use App\Shared\Domain\Enum\TitleTypeEnum;
use App\Shared\Domain\ValueObject\AudioSourceLink;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class YoutubeAudioSourceProvider implements AudioSourceProviderInterface
{
    private const string YOUTUBE_GOOGLEAPIS_DOMAIN = 'https://youtube.googleapis.com';
    private const string WWW_GOOGLEAPIS_DOMAIN = 'https://www.googleapis.com';
    private const string SEARCH_ENDPOINT = '/youtube/v3/search';
    private const string PLAYLIST_ITEMS_ENDPOINT = '/youtube/v3/playlistItems';
    private const string VIDEO_LINK_PREFIX = 'https://www.youtube.com/watch?v=';
    private const string SEARCH_PART_VALUE = 'snippet';
    private const string PLAYLIST_PART_VALUE = 'contentDetails,snippet';
    private const int SEARCH_MAX_RESULTS = 5;
    private const int PLAYLIST_TRACKS_MAX_RESULTS = 50;
    private const float RELEVANCE_THRESHOLD = 0.3;

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $youtubeApiKey,
        private TitleRelevanceScorer $relevanceScorer,
    ) {}

    /**
     * @return AudioSourceLink[]
     */
    public function search(AudioSearchQuery $query): array
    {
        $searchObjectType = SearchObjectTypeEnum::fromTitleType($query->titleType);

        $searchData = $this->get(
            self::YOUTUBE_GOOGLEAPIS_DOMAIN . self::SEARCH_ENDPOINT,
            [
                'query' => [
                    'part' => self::SEARCH_PART_VALUE,
                    'type' => $searchObjectType,
                    'maxResults' => self::SEARCH_MAX_RESULTS,
                    'q' => $this->buildSearchString($query),
                    'key' => $this->youtubeApiKey,
                ]
            ]
        );

        if (!$searchData) {
            return [];
        }

        $searchResultDTO = ApiSearchResultDTO::fromArray($searchData);

        $bestItem = $this->pickBestItem($searchResultDTO->items, $query);

        if ($bestItem === null) {
            return [];
        }

        if ($query->titleType === TitleTypeEnum::Track) {
            return [
                new AudioSourceLink(
                    self::VIDEO_LINK_PREFIX . $bestItem->id->videoId,
                    $bestItem->snippet->title,
                )
            ];
        }

        $playlistData = $this->get(
            self::WWW_GOOGLEAPIS_DOMAIN . self::PLAYLIST_ITEMS_ENDPOINT,
            [
                'query' => [
                    'part' => self::PLAYLIST_PART_VALUE,
                    'playlistId' => $bestItem->id->playlistId,
                    'maxResults' => self::PLAYLIST_TRACKS_MAX_RESULTS,
                    'key' => $this->youtubeApiKey,
                ]
            ]
        );

        if (!$playlistData) {
            return [];
        }

        $linksList = [];

        $playlistResultDTO = ApiPlaylistResultDTO::fromArray($playlistData);

        foreach ($playlistResultDTO->getPlaylistItems() as $playlistItem) {
            $linksList[] = new AudioSourceLink(
                self::VIDEO_LINK_PREFIX . $playlistItem->getVideoId(),
                $playlistItem->getTitle(),
            );
        }

        return $linksList;
    }

    /**
     * @param SearchResultItemDTO[] $items
     */
    private function pickBestItem(array $items, AudioSearchQuery $query): ?SearchResultItemDTO
    {
        $titleQuery = $query->author . ' ' . $query->title;
        $bestScore = 0.0;
        $bestItem = null;

        foreach ($items as $item) {
            $score = $this->relevanceScorer->score($titleQuery, $item->snippet->title);

            if ($query->titleType === TitleTypeEnum::Album
                && $this->channelMatchesAuthor($item->snippet->channelTitle, $query->author)
            ) {
                $score = 1.0;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestItem = $item;
            }

            if ($bestScore >= 1.0) {
                break;
            }
        }

        if ($bestScore < self::RELEVANCE_THRESHOLD) {
            return null;
        }

        return $bestItem;
    }

    private function channelMatchesAuthor(string $channelTitle, string $author): bool
    {
        $normalize = static fn(string $s): string => mb_strtolower(
            preg_replace('/[^\p{L}\p{N}]/u', '', $s),
            'UTF-8'
        );

        return str_contains($normalize($channelTitle), $normalize($author));
    }

    private function buildSearchString(AudioSearchQuery $query): string
    {
        if ($query->titleType === TitleTypeEnum::Album) {
            return $query->author . ' ' . $query->title . ' Full Album';
        }

        return $query->author . ' - ' . $query->title;
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
