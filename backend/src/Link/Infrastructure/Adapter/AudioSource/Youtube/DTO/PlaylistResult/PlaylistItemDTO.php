<?php

declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO\PlaylistResult;

final readonly class PlaylistItemDTO
{
    private function __construct(
        public string $kind,
        public string $etag,
        public string $id,
        public PlaylistItemContentDetailsDTO $contentDetails,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: $data['kind'],
            etag: $data['etag'],
            id: $data['id'],
            contentDetails: PlaylistItemContentDetailsDTO::fromArray($data['contentDetails']),
        );
    }

    public function getVideoId(): string
    {
        return $this->contentDetails->videoId;
    }
}
