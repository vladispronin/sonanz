<?php

declare(strict_types=1);

namespace App\Shared\Application\Event;

use Symfony\Component\Uid\Uuid;

final readonly class TrackFailedEvent
{
    public function __construct(
        public Uuid $trackId,
        public Uuid $jobId,
        public ?Uuid $albumId = null,
    ) {}
}
