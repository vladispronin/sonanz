<?php

declare(strict_types=1);

namespace App\Catalog\Application\MessageHandler;

use App\Catalog\Domain\Port\JobRepositoryInterface;
use App\Shared\Application\Message\CreateJobMessage;
use App\Shared\Application\Message\LinkSearchMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class CreateJobMessageHandler
{
    private JobRepositoryInterface $jobRepository;
    private MessageBusInterface $messageBus;

    public function __construct(
        JobRepositoryInterface $jobRepository,
        MessageBusInterface $messageBus
    )
    {
        $this->jobRepository = $jobRepository;
        $this->messageBus = $messageBus;
    }

    public function __invoke(CreateJobMessage $message): void
    {
        $this->jobRepository->create(
            $message->author,
            $message->title,
            $message->titleType,
            $message->id,
        );

        $this->jobRepository->start($message->id);

        $this->messageBus->dispatch(new LinkSearchMessage(
            $message->id,
            $message->author,
            $message->title,
            $message->titleType,
        ));
    }
}
