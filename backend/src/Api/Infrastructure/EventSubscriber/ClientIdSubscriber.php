<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Uid\Uuid;

final class ClientIdSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST  => ['onRequest', 256],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        $clientId = $request->headers->get('X-Client-ID');

        if (empty($clientId)) {
            $this->logger->error('Ошибка аутентификации', [
                'method'    => $request->getMethod(),
                'client_id' => $clientId
            ]);

            $event->setResponse(new JsonResponse([
                'message' => 'Authentication failed',
                'code' => 401,
            ]));
        }

        $request->attributes->set('clientId', Uuid::fromString($clientId));
    }
}
