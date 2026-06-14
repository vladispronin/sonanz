<?php
declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\AudioSource\Youtube;

use App\Link\Domain\Enum\TitleTypeEnum;
use App\Link\Domain\Port\AudioSourceProviderInterface;
use App\Link\Domain\ValueObject\AudioSourceLink;
use App\Link\Domain\ValueObject\LinkSearchQuery;
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
        $searchObjectType = match ($query->titleType) {
            TitleTypeEnum::Track => 'video',
            TitleTypeEnum::Album => 'playlist',
        };

        $searchData = $this->get(
            self::YOUTUBE_GOOGLEAPIS_DOMAIN . self::SEARCH_ENDPOINT,
            [
                'part' => 'snippet',
                'type' => $searchObjectType,
                'maxResults' => 1,
                'q' => $query->author . ' - ' . $query->title,
                'key' => env('YOUTUBE_API_KEY'),
            ]
        );

        if (!$searchData) {
            return [];
        }

        $idType = match ($query->titleType) {
            TitleTypeEnum::Track => 'videoId',
            TitleTypeEnum::Album => 'playlistId',
        };

        $mediaId = $searchData['items'][0]['id'][$idType];

        if ($query->titleType === TitleTypeEnum::Track) {
            return [
                new AudioSourceLink(self::VIDEO_LINK_PREFIX . $mediaId)
            ];
        }

        $playlistData = $this->get(
            self::WWW_GOOGLEAPIS_DOMAIN . self::PLAYLIST_ITEMS_ENDPOINT,
            [
                'part' => 'contentDetails',
                'playlistId' => $mediaId,
                'maxResults' => 50,
                'key' => env('YOUTUBE_API_KEY'),
            ]
        );

        if (!$playlistData) {
            return [];
        }

        $linksList = [];

        foreach ($playlistData['items'] as $playlistItem) {
            $linksList[] = new AudioSourceLink($playlistItem['contentDetails']['videoId']);
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
