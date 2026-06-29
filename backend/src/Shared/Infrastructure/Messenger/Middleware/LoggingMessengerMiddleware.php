<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Messenger\Middleware;

use App\Shared\Infrastructure\Logging\CorrelationIdStorage;
use App\Shared\Infrastructure\Messenger\Stamp\CorrelationIdStamp;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

final class LoggingMessengerMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CorrelationIdStorage $storage,
    ) {}

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $messageClass = $envelope->getMessage()::class;
        $shortName = substr(strrchr($messageClass, '\\'), 1);
        $isHandling = $envelope->last(ReceivedStamp::class) !== null;

        if (!$isHandling) {
            if ($envelope->last(CorrelationIdStamp::class) === null) {
                $correlationId = $this->storage->get() ?: 'internal-' . substr(md5(uniqid()), 0, 8);
                $envelope = $envelope->with(new CorrelationIdStamp($correlationId));
            }

            $this->logger->info("Сообщение отправлено в шину: {$shortName}", [
                'message_class' => $messageClass,
            ]);

            return $stack->next()->handle($envelope, $stack);
        }

        $stamp = $envelope->last(CorrelationIdStamp::class);
        if ($stamp !== null) {
            $this->storage->set($stamp->correlationId);
        }

        $start = microtime(true);
        $this->logger->info("Обработка сообщения начата: {$shortName}", [
            'message_class' => $messageClass,
        ]);

        try {
            $result = $stack->next()->handle($envelope, $stack);

            $ms = (int) ((microtime(true) - $start) * 1000);
            $this->logger->info("Обработка сообщения завершена: {$shortName} ({$ms}мс)", [
                'message_class' => $messageClass,
                'duration_ms'   => $ms,
            ]);

            return $result;
        } catch (\Throwable $e) {
            $ms = (int) ((microtime(true) - $start) * 1000);
            $this->logger->error("Ошибка обработки сообщения: {$shortName} — {$e->getMessage()}", [
                'message_class' => $messageClass,
                'duration_ms'   => $ms,
                'exception'     => $e,
            ]);
            throw $e;
        }
    }
}
