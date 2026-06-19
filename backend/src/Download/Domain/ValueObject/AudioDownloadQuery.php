<?php

declare(strict_types=1);

namespace App\Download\Domain\ValueObject;

use Symfony\Component\Uid\Uuid;

class AudioDownloadQuery
{
    public function __construct(
        public string $url,
        public Uuid $trackId
    ) {}
}
