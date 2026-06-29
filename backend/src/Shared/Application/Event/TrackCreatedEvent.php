<?php

declare(strict_types=1);

namespace App\Shared\Application\Event;

use Symfony\Component\Uid\Uuid;

final readonly class TrackCreatedEvent
{
    public function __construct(
        public Uuid $trackId,
        public Uuid $jobId,
        public string $url,
        public ?Uuid $albumId = null,
    ) {}
}
