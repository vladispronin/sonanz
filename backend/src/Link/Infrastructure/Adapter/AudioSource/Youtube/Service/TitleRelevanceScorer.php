<?php

declare(strict_types=1);

namespace App\Link\Infrastructure\Adapter\AudioSource\Youtube\Service;

final class TitleRelevanceScorer
{
    private const array NOISE_WORDS = [
        'official', 'audio', 'video', 'lyrics', 'hd', 'full',
        'album', 'remastered', 'feat', 'ft', 'live', 'cover',
        'remix', 'version', 'single', 'extended',
    ];

    public function score(string $query, string $candidate): float
    {
        $queryTokens = $this->normalize($query);
        $candidateTokens = $this->normalize($candidate);

        return $this->jaccard($queryTokens, $candidateTokens);
    }

    private function normalize(string $s): array
    {
        $s = mb_strtolower($s, 'UTF-8');
        $s = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $s);
        $tokens = preg_split('/\s+/', trim($s), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_filter(
            $tokens ?? [],
            static fn (string $token): bool => !in_array($token, self::NOISE_WORDS, true),
        ));
    }

    private function jaccard(array $a, array $b): float
    {
        if (empty($a) && empty($b)) {
            return 0.0;
        }

        $a = array_unique($a);
        $b = array_unique($b);

        $intersection = array_intersect($a, $b);
        $union = array_unique(array_merge($a, $b));

        return count($intersection) / count($union);
    }
}
