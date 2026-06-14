<?php

declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO\PlaylistResult;

use App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO\PageInfoDTO;

final readonly class ApiPlaylistResultDTO
{
    /**
     * @param PlaylistItemDTO[] $items
     */
    private function __construct(
        public string $kind,
        public string $etag,
        public array $items,
        public PageInfoDTO $pageInfo,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: $data['kind'],
            etag: $data['etag'],
            items: array_map(
                static fn (array $item): PlaylistItemDTO => PlaylistItemDTO::fromArray($item),
                $data['items'],
            ),
            pageInfo: PageInfoDTO::fromArray($data['pageInfo']),
        );
    }

    public function getPlaylistItems(): array
    {
        return $this->items;
    }
}
