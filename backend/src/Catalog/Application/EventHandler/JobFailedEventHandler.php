<?php

declare(strict_types=1);

namespace App\Catalog\Application\EventHandler;

use App\Catalog\Domain\Port\JobRepositoryInterface;
use App\Shared\Application\Event\JobFailedEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class JobFailedEventHandler
{
    private JobRepositoryInterface $jobRepository;

    public function __construct(JobRepositoryInterface $jobRepository)
    {
        $this->jobRepository = $jobRepository;
    }

    public function __invoke(JobFailedEvent $event): void
    {
        $this->jobRepository->fail($event->jobId);

        throw new UnrecoverableMessageHandlingException(
            sprintf('Job %s failed: %s', $event->jobId->toString(), $event->errorMessage)
        );
    }
}
