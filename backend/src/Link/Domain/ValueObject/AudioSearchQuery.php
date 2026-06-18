<?php

declare(strict_types=1);

namespace App\Link\Domain\ValueObject;

use App\Shared\Domain\Enum\TitleTypeEnum;

final readonly class AudioSearchQuery
{
    private function __construct(
        public string $author,
        public string $title,
        public TitleTypeEnum $titleType,
    ) {}

    public static function fromRawInput(string $author, string $title, TitleTypeEnum $titleType): AudioSearchQuery
    {
        $author = self::normalizeString($author);
        $title = self::normalizeString($title);

        self::validate($author, $title);

        return new self($author, $title, $titleType);
    }

    private static function normalizeString(string $value): string
    {
        return $value;
    }

    private static function validate(string $author, string $title): void
    {

    }
}
