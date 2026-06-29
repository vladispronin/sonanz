<?php

declare(strict_types=1);

namespace App\Shared\Application\Event;

use App\Shared\Domain\ValueObject\TrackTagMetadata;
use Symfony\Component\Uid\Uuid;

final readonly class AudioTaggedEvent
{
    public function __construct(
        public Uuid $trackId,
        public ?TrackTagMetadata $metadata = null,
    ) {}
}
