<?php

declare(strict_types=1);

namespace App\Shared\Application\Message;

use Symfony\Component\Uid\Uuid;

class DownloadFileMessage
{
    public function __construct(
        public Uuid $jobId,
    ) {}
}
