<?php

declare(strict_types=1);

namespace App\Tag\Domain\Port;

use App\Tag\Domain\ValueObject\AudioTagQuery;
use App\Tag\Domain\ValueObject\TrackMetadata;

interface ID3TagsProviderInterface
{
    public function getTags(AudioTagQuery $query): ?TrackMetadata;
}
