<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject;

class JobProgress
{
    public const int JOB_INITIATED = 0;
    public const int LINKS_FOUND = 20;
    public const int MEDIA_DOWNLOADED = 60;
    public const int MEDIA_TAGGED = 80;
    public const int JOB_COMPLETED = 100;
}
