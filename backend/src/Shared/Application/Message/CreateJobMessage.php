<?php

declare(strict_types=1);

namespace App\Shared\Application\Message;

use App\Shared\Domain\Enum\TitleTypeEnum;
use Symfony\Component\Uid\Uuid;

final readonly class CreateJobMessage
{
    public function __construct(
        public Uuid $id,
        public string $author,
        public string $title,
        public TitleTypeEnum $titleType,
        public bool $withMetadata,
    ) {}
}
