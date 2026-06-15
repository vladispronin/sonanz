<?php

declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO\SearchResult;

use App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO\PageInfoDTO;
use App\Link\Infrastructure\Adapter\AudioSource\Youtube\Enum\SearchObjectIdTypeEnum;

final readonly class ApiSearchResultDTO
{
    /**
     * @param SearchResultItemDTO[] $items
     */
    private function __construct(
        public string $kind,
        public string $etag,
        public ?string $nextPageToken,
        public ?string $regionCode,
        public PageInfoDTO $pageInfo,
        public array $items,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: $data['kind'],
            etag: $data['etag'],
            nextPageToken: $data['nextPageToken'] ?? null,
            regionCode: $data['regionCode'] ?? null,
            pageInfo: PageInfoDTO::fromArray($data['pageInfo']),
            items: array_map(
                static fn (array $item): SearchResultItemDTO => SearchResultItemDTO::fromArray($item),
                $data['items'],
            ),
        );
    }

    public function getMediaId(SearchObjectIdTypeEnum $searchObjectIdType): string
    {
        $id = $this->items[0]->id;

        return match ($searchObjectIdType) {
            SearchObjectIdTypeEnum::VideoId => $id->videoId,
            SearchObjectIdTypeEnum::PlaylistId => $id->playlistId,
        } ?? throw new \LogicException('Unexpected null id for given search object type');
    }

    public function getTitle(): string
    {
        return $this->items[0]->snippet->title;
    }
}
