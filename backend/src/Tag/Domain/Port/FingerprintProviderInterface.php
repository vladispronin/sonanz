<?php

declare(strict_types=1);

namespace App\Tag\Domain\Port;

use App\Tag\Domain\ValueObject\AudioFingerprint;

interface FingerprintProviderInterface
{
    public function getFingerprint(string $filePath): AudioFingerprint;
}
