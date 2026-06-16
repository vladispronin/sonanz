<?php

declare(strict_types=1);

namespace App\Catalog\Application\Message;

final readonly class CreateAlbumCommand
{
    /** @param string[] $trackUrls */
    public function __construct(
        public string $jobId,
        public array $trackUrls,
    ) {}
}
