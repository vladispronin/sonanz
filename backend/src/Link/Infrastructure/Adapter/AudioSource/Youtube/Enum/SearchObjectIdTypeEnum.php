<?php

declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\AudioSource\Youtube\Enum;

use App\Link\Domain\Enum\TitleTypeEnum;

enum SearchObjectIdTypeEnum: string
{
    case VideoId = 'videoId';
    case PlaylistId = 'playlistId';

    public static function fromTitleType(TitleTypeEnum $titleType): SearchObjectIdTypeEnum
    {
        return match ($titleType) {
            TitleTypeEnum::Track => self::VideoId,
            TitleTypeEnum::Album => self::PlaylistId,
        };
    }
}
