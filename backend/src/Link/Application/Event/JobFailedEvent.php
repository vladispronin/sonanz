<?php

declare(strict_types=1);

namespace App\Link\Application\Event;

use Symfony\Component\Uid\Uuid;

final readonly class JobFailedEvent
{
    public const string LINKS_NOT_FOUND = 'LINKS_NOT_FOUND';
    public const string UNKNOWN_ERROR = 'UNKNOWN_ERROR';

    public function __construct(
        public Uuid $jobId,
        public string $errorMessage = self::UNKNOWN_ERROR,
    ) {}
}
