<?php

declare(strict_types=1);

namespace App\Download\Infrastructure\Adapter\AudioDownload;

use App\Download\Domain\Port\AudioDownloadProviderInterface;
use App\Download\Domain\ValueObject\AudioDownloadQuery;
use RuntimeException;
use Throwable;

final readonly class ChainAudioDownloadProvider implements AudioDownloadProviderInterface
{
    /** @param iterable<AudioDownloadProviderInterface> $providers */
    public function __construct(private iterable $providers) {}

    /**
     * @throws Throwable
     */
    public function download(AudioDownloadQuery $query): void
    {
        $lastException = null;

        foreach ($this->providers as $provider) {
            try {
                $provider->download($query);
                return;
            } catch (Throwable $e) {
                $lastException = $e;
            }
        }

        throw $lastException ?? new RuntimeException('All audio download providers failed');
    }
}

