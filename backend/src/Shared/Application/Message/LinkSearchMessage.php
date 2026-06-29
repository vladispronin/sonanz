<?php

declare(strict_types=1);

namespace App\Shared\Application\Message;

use App\Shared\Domain\Enum\TitleTypeEnum;
use Symfony\Component\Uid\Uuid;

final readonly class LinkSearchMessage
{
    public function __construct(
        public Uuid $jobId,
        public string $author,
        public string $title,
        public TitleTypeEnum $titleType,
    ) {}
}
