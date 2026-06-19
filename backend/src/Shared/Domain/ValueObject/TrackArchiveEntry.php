<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use Symfony\Component\Uid\Uuid;

final readonly class TrackArchiveEntry
{
    public function __construct(
        public Uuid $id,
        public string $title,
    ) {}
}
