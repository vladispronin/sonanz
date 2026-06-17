<?php

declare(strict_types=1);

namespace App\Link\Application\Event;

final readonly class JobFailedEvent
{
    public const string LINKS_NOT_FOUND = 'LINKS_NOT_FOUND';
    public const string UNKNOWN_ERROR = 'UNKNOWN_ERROR';

    public function __construct(
        public string $jobId,
        public string $errorMessage = self::UNKNOWN_ERROR,
    ) {}
}
