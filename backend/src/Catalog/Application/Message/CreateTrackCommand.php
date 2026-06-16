<?php

declare(strict_types=1);

namespace App\Catalog\Application\Message;

final readonly class CreateTrackCommand
{
    public function __construct(
        public string $jobId,
        public string $url,
    ) {}
}
