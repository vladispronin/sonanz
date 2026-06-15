<?php

declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\Metadata;

use App\Link\Domain\Port\MetadataProviderInterface;
use App\Link\Domain\ValueObject\AudioSearchQuery;

final readonly class ChainMetadataProvider implements MetadataProviderInterface
{
    /** @param iterable<MetadataProviderInterface> $providers */
    public function __construct(private iterable $providers) {}

    /**
     * @return
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
