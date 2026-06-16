<?php

declare(strict_types=1);

namespace App\Catalog\Application\Event;

final readonly class TrackCreatedEvent
{
    public function __construct(
        public string $trackId,
        public string $jobId,
        public string $url,
        public ?string $albumId = null,
    ) {}
}
