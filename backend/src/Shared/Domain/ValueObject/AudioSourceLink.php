<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

final readonly class AudioSourceLink
{
    public function __construct(
        public string $url,
        public string $title,
    ) {}
}
