<?php

declare(strict_types=1);

namespace App\Tag\Infrastructure\Adapter;

use App\Tag\Domain\Port\ID3TagsProviderInterface;
use App\Tag\Domain\ValueObject\AudioTagQuery;
use App\Tag\Domain\ValueObject\TrackMetadata;

final readonly class ChainID3TagsProvider implements ID3TagsProviderInterface
{
    /** @param iterable<ID3TagsProviderInterface> $providers */
    public function __construct(private iterable $providers) {}

    public function getTags(AudioTagQuery $query): ?TrackMetadata
    {
        foreach ($this->providers as $provider) {
            $result = $provider->getTags($query);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }
}
