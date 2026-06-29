<?php

declare(strict_types=1);

namespace App\Catalog\Application\EventHandler;

use App\Catalog\Domain\Port\JobRepositoryInterface;
use App\Catalog\Domain\ValueObject\JobProgress;
use App\Shared\Application\Event\AlbumArchivedEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AlbumArchivedEventHandler
{
    private JobRepositoryInterface $jobRepository;

    public function __construct(
        JobRepositoryInterface $jobRepository,
    ) {
        $this->jobRepository = $jobRepository;
    }

    public function __invoke(AlbumArchivedEvent $event): void
    {
        $this->jobRepository->updateProgress($event->jobId, JobProgress::JOB_COMPLETED);

        $this->jobRepository->complete($event->jobId);
    }
}
