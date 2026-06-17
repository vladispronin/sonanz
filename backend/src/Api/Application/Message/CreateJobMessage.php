<?php

declare(strict_types=1);

namespace App\Api\Application\Message;

use App\Link\Domain\Enum\TitleTypeEnum;
use Symfony\Component\Uid\Uuid;

class CreateJobMessage
{
    public function __construct(
        public Uuid $id,
        public string $author,
        public string $title,
        public TitleTypeEnum $titleType,
    ) {}
}
