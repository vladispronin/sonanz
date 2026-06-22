<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Logging;

final class CorrelationIdStorage
{
    private string $id = '';

    public function get(): string
    {
        return $this->id;
    }

    public function set(string $id): void
    {
        $this->id = $id;
    }

    public function has(): bool
    {
        return $this->id !== '';
    }
}
