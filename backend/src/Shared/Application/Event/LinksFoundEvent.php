<?php

declare(strict_types=1);

namespace App\Shared\Application\Event;

use App\Shared\Domain\Enum\TitleTypeEnum;
use App\Shared\Domain\ValueObject\AudioSourceLink;
use Symfony\Component\Uid\Uuid;

final readonly class LinksFoundEvent
{
    /** @param AudioSourceLink[] $audioSourceLinks */
    public function __construct(
        public Uuid $jobId,
        public TitleTypeEnum $titleType,
        public array $audioSourceLinks,
    ) {}
}
