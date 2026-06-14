<?php

declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO\SearchResult;

final readonly class SnippetDTO
{
    private function __construct(
        public string $publishedAt,
        public string $channelId,
        public string $title,
        public string $description,
        public ThumbnailSetDTO $thumbnails,
        public string $channelTitle,
        public string $liveBroadcastContent,
        public string $publishTime,
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
            liveBroadcastContent: $data['liveBroadcastContent'],
            publishTime: $data['publishTime'],
        );
    }
}
