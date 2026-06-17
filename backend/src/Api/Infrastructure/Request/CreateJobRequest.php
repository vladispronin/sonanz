<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\Request;

use App\Link\Domain\Enum\TitleTypeEnum;

final readonly class CreateJobRequest
{
    public function __construct(
        public string $author,
        public string $title,
        public TitleTypeEnum $titleType,
    ) {}
}
