<?php

declare(strict_types=1);

namespace App\Tag\Domain\ValueObject;

final readonly class AudioTagQuery
{
    public function __construct(
        public AudioFingerprint $fingerprint,
        public string $artist,
        public string $title,
    ) {}
}
