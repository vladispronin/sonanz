<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\EventSubscriber;

use App\Shared\Infrastructure\Logging\CorrelationIdStorage;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Uid\Uuid;

final class HttpLoggingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CorrelationIdStorage $storage,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST  => ['onRequest', 256],
            KernelEvents::RESPONSE => ['onResponse', -256],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        $correlationId = $request->headers->get('X-Correlation-ID') ?? Uuid::v4()->toString();
        $this->storage->set($correlationId);
        $request->attributes->set('_log_start_time', microtime(true));

        $this->logger->info('Входящий HTTP-запрос', [
            'method'    => $request->getMethod(),
            'path'      => $request->getPathInfo(),
            'query'     => $request->getQueryString(),
            'client_ip' => $request->getClientIp(),
        ]);
    }

    public function onResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request  = $event->getRequest();
        $response = $event->getResponse();

        $startTime = $request->attributes->get('_log_start_time', microtime(true));
        $ms = (int) ((microtime(true) - $startTime) * 1000);

        $response->headers->set('X-Correlation-ID', $this->storage->get());

        $this->logger->info('HTTP-ответ отправлен', [
            'method'      => $request->getMethod(),
            'path'        => $request->getPathInfo(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $ms,
        ]);
    }
}
