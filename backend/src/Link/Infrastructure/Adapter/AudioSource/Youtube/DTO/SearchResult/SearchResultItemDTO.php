<?php

declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO\SearchResult;

final readonly class SearchResultItemDTO
{
    private function __construct(
        public string $kind,
        public string $etag,
        public SearchResultIdDTO $id,
        public SnippetDTO $snippet,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: $data['kind'],
            etag: $data['etag'],
            id: SearchResultIdDTO::fromArray($data['id']),
            snippet: SnippetDTO::fromArray($data['snippet']),
        );
    }
}
