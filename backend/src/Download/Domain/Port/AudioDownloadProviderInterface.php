<?php

declare(strict_types=1);

namespace App\Download\Domain\Port;

use App\Download\Domain\ValueObject\AudioDownloadQuery;

interface AudioDownloadProviderInterface
{
    public function download(AudioDownloadQuery $query): void;
}
