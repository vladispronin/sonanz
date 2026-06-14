<?php

declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO\SearchResult;

final readonly class ThumbnailSetDTO
{
    private function __construct(
        public ThumbnailDTO $default,
        public ThumbnailDTO $medium,
        public ThumbnailDTO $high,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            default: ThumbnailDTO::fromArray($data['default']),
            medium: ThumbnailDTO::fromArray($data['medium']),
            high: ThumbnailDTO::fromArray($data['high']),
        );
    }
}
