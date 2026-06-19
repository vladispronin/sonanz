<?php

declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO;

final readonly class ThumbnailSetDTO
{
    private function __construct(
        public ?ThumbnailDTO $default,
        public ?ThumbnailDTO $medium,
        public ?ThumbnailDTO $high,
        public ?ThumbnailDTO $standard,
        public ?ThumbnailDTO $maxres,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            default: isset($data['default']) ? ThumbnailDTO::fromArray($data['default']) : null,
            medium: isset($data['medium']) ? ThumbnailDTO::fromArray($data['medium']) : null,
            high: isset($data['high']) ? ThumbnailDTO::fromArray($data['high']) : null,
            standard: isset($data['standard']) ? ThumbnailDTO::fromArray($data['standard']) : null,
            maxres: isset($data['maxres']) ? ThumbnailDTO::fromArray($data['maxres']) : null,
        );
    }
}
