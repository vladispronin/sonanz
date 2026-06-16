<?php

declare(strict_types=1);

namespace App\Link\Domain\ValueObject;

final readonly class AudioSourceLink
{
    public function __construct(
        public string $url,
        public string $title,
    ) {}
}
