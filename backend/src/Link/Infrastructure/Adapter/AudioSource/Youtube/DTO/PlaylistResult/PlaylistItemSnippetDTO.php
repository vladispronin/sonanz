<?php

declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO\PlaylistResult;

use App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO\ThumbnailSetDTO;

final readonly class PlaylistItemSnippetDTO
{
    private function __construct(
        public string $publishedAt,
        public string $channelId,
        public string $title,
        public string $description,
        public ThumbnailSetDTO $thumbnails,
        public string $channelTitle,
        public string $playlistId,
        public int $position,
        public PlaylistItemResourceIdDTO $resourceId,
        public string $videoOwnerChannelTitle,
        public string $videoOwnerChannelId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            publishedAt: $data['publishedAt'],
            channelId: $data['channelId'],
            title: $data['title'],
            description: $data['description'],
            thumbnails: ThumbnailSetDTO::fromArray($data['thumbnails']),
            channelTitle: $data['channelTitle'],
            playlistId: $data['playlistId'],
            position: $data['position'],
            resourceId: PlaylistItemResourceIdDTO::fromArray($data['resourceId']),
            videoOwnerChannelTitle: $data['videoOwnerChannelTitle'],
            videoOwnerChannelId: $data['videoOwnerChannelId'],
        );
    }
}
