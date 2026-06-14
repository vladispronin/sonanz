<?php

declare(strict_types=1);

use App\Link\Domain\Port\AudioSourceProviderInterface;
use App\Link\Domain\ValueObject\LinkSearchQuery;
use App\Link\Domain\ValueObject\AudioSourceLink;

final class ChainAudioSourceProvider implements AudioSourceProviderInterface
{
    /** @param iterable<AudioSourceProviderInterface> $providers */
    public function __construct(private readonly iterable $providers) {}

    /**
     * @return AudioSourceLink[]
     */
    public function search(LinkSearchQuery $query): array
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

