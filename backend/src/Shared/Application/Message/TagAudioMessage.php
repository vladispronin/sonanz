<?php

declare(strict_types=1);

namespace App\Shared\Application\Message;

use Symfony\Component\Uid\Uuid;

final readonly class TagAudioMessage
{
    public function __construct(
        public Uuid $trackId
    ) {}
}
