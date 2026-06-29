<?php

declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\AudioSource\Youtube\Enum;

use App\Shared\Domain\Enum\TitleTypeEnum;

enum SearchObjectTypeEnum: string
{
    case Video = 'video';
    case Playlist = 'playlist';

    public static function fromTitleType(TitleTypeEnum $titleType): SearchObjectTypeEnum
    {
        return match ($titleType) {
            TitleTypeEnum::Track => self::Video,
            TitleTypeEnum::Album => self::Playlist,
        };
    }
}
