<?php

declare(strict_types=1);

namespace App\Shared\Application\Event;

use Symfony\Component\Uid\Uuid;

class AlbumArchivedEvent
{
    public function __construct(
        public Uuid $jobId
    ) {}
}
