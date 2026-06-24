<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

final class SensitiveDataProcessor implements ProcessorInterface
{
    private const string PATTERN = '/([?&](?:key|api_key|apikey|token|secret|client)=)[^&\s"\']+/i';

    public function __invoke(LogRecord $record): LogRecord
    {
        return $record->with(
            message: $this->mask($record->message),
            context: $this->maskArray($record->context),
        );
    }

    private function mask(string $value): string
    {
        return preg_replace(self::PATTERN, '$1***', $value);
    }

    private function maskArray(array $data): array
    {
        foreach ($data as $k => $v) {
            $data[$k] = match (true) {
                is_string($v) => $this->mask($v),
                is_array($v)  => $this->maskArray($v),
                default       => $v,
            };
        }
        return $data;
    }
}
