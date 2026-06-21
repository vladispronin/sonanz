<?php

declare(strict_types=1);

namespace App\Tag\Domain\ValueObject;

final readonly class AudioFingerprint
{
    public function __construct(
        public string $fingerprint,
        public int $duration,
    ) {}
}
