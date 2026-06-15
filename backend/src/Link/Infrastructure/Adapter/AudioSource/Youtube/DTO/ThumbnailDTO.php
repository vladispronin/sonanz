<?php

declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\AudioSource\Youtube\DTO;

final readonly class ThumbnailDTO
{
    private function __construct(
        public string $url,
        public int $width,
        public int $height,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            url: $data['url'],
            width: $data['width'],
            height: $data['height'],
        );
    }
}
