<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly LoggerInterface $logger) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onException'];
    }

    public function onException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $this->logger->error('Необработанное исключение: ' . $exception->getMessage(), [
            'exception_class' => $exception::class,
            'file'            => $exception->getFile(),
            'line'            => $exception->getLine(),
        ]);

        $event->setResponse(new JsonResponse(
            ['error' => $exception->getMessage()],
            500,
        ));
    }
}
