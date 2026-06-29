<?php

declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO;

final readonly class PageInfoDTO
{
    private function __construct(
        public int $totalResults,
        public int $resultsPerPage,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            totalResults: $data['totalResults'],
            resultsPerPage: $data['resultsPerPage'],
        );
    }
}
