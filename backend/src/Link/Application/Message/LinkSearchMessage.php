<?php

declare(strict_types=1);

namespace App\Link\Application\Message;

use App\Link\Domain\Enum\TitleTypeEnum;

final readonly class LinkSearchMessage
{
    public string $jobId;
    public string $author;
    public string $title;
    public TitleTypeEnum $titleType;
}
