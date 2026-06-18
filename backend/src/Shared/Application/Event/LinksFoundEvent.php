<?php

declare(strict_types=1);

namespace App\Shared\Application\Event;

use App\Shared\Domain\Enum\TitleTypeEnum;
use Symfony\Component\Uid\Uuid;

final readonly class LinksFoundEvent
{
    /** @param string[] $urls */
    public function __construct(
        public Uuid $jobId,
        public TitleTypeEnum $titleType,
        public array $urls,
    ) {}
}
