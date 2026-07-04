<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\EventSubscriber;

use App\Api\Infrastructure\Attribute\PublicRoute;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Log\LoggerInterface;
use ReflectionMethod;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Uid\Uuid;
use Throwable;

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
            KernelEvents::REQUEST  => ['onRequest', 8],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $controller = $request->attributes->get('_controller');
        if (is_string($controller) && str_contains($controller, '::')) {
            [$class, $method] = explode('::', $controller);
            $reflection = new ReflectionMethod($class, $method);
            if (!empty($reflection->getAttributes(PublicRoute::class))) {
                return;
            }
        }

        $authHeader = $request->headers->get('Authorization');

        $token = str_replace('Bearer ', '', $authHeader);

        $errorCode = null;

        try {
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
        } catch (ExpiredException) {
            $errorCode = 'TOKEN_EXPIRED';
        } catch (Throwable) {
            $errorCode = 'AUTH_FAILED';
        }

        if ($errorCode !== null) {
            $this->logger->error('Ошибка аутентификации', [
                'code'   => $errorCode,
                'method' => $request->getMethod(),
            ]);

            $event->setResponse(new JsonResponse([
                'message' => 'Authentication failed',
                'code'    => $errorCode,
            ], 401));

            return;
        }

        if (isset($decoded)) {
            $request->attributes->set('clientId', Uuid::fromString($decoded->sub));
        }
    }
}
