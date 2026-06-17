<?php

declare(strict_types=1);

namespace App\Catalog\Application\Event;

use Symfony\Component\Uid\Uuid;

final readonly class TrackCreatedEvent
{
    public function __construct(
        public string $trackId,
        public Uuid $jobId,
        public string $url,
        public ?string $albumId = null,
    ) {}
}
