<?php

declare(strict_types=1);

namespace App\Tag\Infrastructure\Adapter\Chromaprint;

use App\Tag\Domain\Port\FingerprintProviderInterface;
use App\Tag\Domain\ValueObject\AudioFingerprint;
use Symfony\Component\Process\Process;

class ChromaprintFingerprintProvider implements FingerprintProviderInterface
{
    public function getFingerprint(string $filePath): AudioFingerprint
    {
        $process = new Process(['fpcalc', '-json', $filePath]);
        $process->run();

        $result = json_decode($process->getOutput(), true);

        return new AudioFingerprint(
            fingerprint: $result['fingerprint'],
            duration: (int) $result['duration'],
        );
    }
}
