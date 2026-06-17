<?php

declare(strict_types=1);

namespace App\Catalog\Application\MessageHandler;

use App\Api\Application\Message\CreateJobMessage;
use App\Catalog\Domain\Port\JobRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateJobMessageHandler
{
    private JobRepositoryInterface $jobRepository;

    public function __construct(JobRepositoryInterface $jobRepository)
    {
        $this->jobRepository = $jobRepository;
    }

    public function __invoke(CreateJobMessage $message): void
    {
        $this->jobRepository->create($message->id);
    }
}
