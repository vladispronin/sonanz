<?php

declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\AudioSource;

use App\Link\Domain\Port\AudioSourceProviderInterface;
use App\Link\Domain\ValueObject\AudioSearchQuery;
use App\Shared\Domain\ValueObject\AudioSourceLink;

final readonly class ChainAudioSourceProvider implements AudioSourceProviderInterface
{
    /** @param iterable<AudioSourceProviderInterface> $providers */
    public function __construct(private iterable $providers) {}

    /**
     * @return AudioSourceLink[]
     */
    public function search(AudioSearchQuery $query): array
    {
        foreach ($this->providers as $provider) {
            $result = $provider->search($query);
            if ($result !== []) {
                return $result;
            }
        }

        return [];
    }
}

