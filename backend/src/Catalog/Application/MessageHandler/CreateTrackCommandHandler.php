<?php

declare(strict_types=1);

namespace App\Catalog\Application\MessageHandler;

use App\Catalog\Application\Event\TrackCreatedEvent;
use App\Catalog\Application\Message\CreateTrackCommand;
use App\Catalog\Domain\Port\TrackRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class CreateTrackCommandHandler
{
    public function __construct(
        private TrackRepositoryInterface $trackRepository,
        private MessageBusInterface $bus,
    ) {}

    public function __invoke(CreateTrackCommand $command): void
    {
        $track = $this->trackRepository->create($command->jobId, $command->url, null);

        $this->bus->dispatch(new TrackCreatedEvent(
            trackId: $track->getId()->toString(),
            jobId: $command->jobId,
            url: $command->url,
        ));
    }
}
