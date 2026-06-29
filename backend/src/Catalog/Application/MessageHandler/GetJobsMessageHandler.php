<?php

declare(strict_types=1);

namespace App\Catalog\Application\MessageHandler;

use App\Catalog\Domain\Entity\Job;
use App\Catalog\Domain\Port\JobRepositoryInterface;
use App\Shared\Application\Message\GetJobsMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetJobsMessageHandler
{
    private JobRepositoryInterface $jobRepository;

    public function __construct(
        JobRepositoryInterface $jobRepository
    ) {
        $this->jobRepository = $jobRepository;
    }

    public function __invoke(GetJobsMessage $message): array
    {
        $jobs = $this->jobRepository->findBy([
            'userId' => $message->userId,
        ]);

        return array_map(fn (Job $job) => [
            'id' => $job->getId()->toString(),
            'status' => $job->getStatus()->value,
            'progress' => $job->getProgress(),
        ], $jobs);
    }
}
