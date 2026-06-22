<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

final class CorrelationIdProcessor implements ProcessorInterface
{
    public function __construct(private readonly CorrelationIdStorage $storage) {}

    public function __invoke(LogRecord $record): LogRecord
    {
        return $record->with(extra: [
            ...$record->extra,
            'correlation_id' => $this->storage->get() ?: 'без-id',
        ]);
    }
}
