<?php

declare(strict_types=1);

namespace App\Shared\Application\Message;

use Symfony\Component\Uid\Uuid;

final readonly class GetJobsMessage
{
    public function __construct(
        public Uuid $userId,
    ) {}
}
