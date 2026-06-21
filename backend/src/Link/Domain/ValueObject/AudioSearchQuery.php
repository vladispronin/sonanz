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
        $value = trim($value);
        $value = preg_replace('/\s+/', ' ', $value);
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    private static function validate(string $author, string $title): void
    {
        foreach (['author' => $author, 'title' => $title] as $field => $value) {
            $length = mb_strlen($value);
            if (!$length) {
                throw new \InvalidArgumentException("Field $field shouldn't be empty.");
            }
            if ($length > 200) {
                throw new \InvalidArgumentException("Field $field is too long.");
            }
        }
    }
}
