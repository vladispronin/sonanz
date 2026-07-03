<?php

declare(strict_types=1);

namespace App\Shared\Application\Dto;

class DownloadFileDto
{
    public function __construct(
        public string $path,
        public string $mediaName,
    ) {}
}
