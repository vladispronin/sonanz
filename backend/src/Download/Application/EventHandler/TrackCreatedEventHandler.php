<?php

declare(strict_types=1);

namespace App\Download\Application\EventHandler;

use App\Download\Domain\Port\AudioDownloadProviderInterface;
use App\Download\Domain\ValueObject\AudioDownloadQuery;
use App\Shared\Application\Event\AudioDownloadedEvent;
use App\Shared\Application\Event\TrackCreatedEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
class TrackCreatedEventHandler
{
    private AudioDownloadProviderInterface $audioDownloadProvider;
    private MessageBusInterface $messageBus;

    public function __construct(
        AudioDownloadProviderInterface $audioDownloadProvider,
        MessageBusInterface $messageBus
    )
    {
        $this->audioDownloadProvider = $audioDownloadProvider;
        $this->messageBus = $messageBus;
    }
    public function __invoke(TrackCreatedEvent $event): void
    {
        $audioDownloadQuery = new AudioDownloadQuery($event->url, $event->trackId);

        $this->audioDownloadProvider->download($audioDownloadQuery);

        $this->messageBus->dispatch(new AudioDownloadedEvent($event->trackId, $event->jobId, $event->albumId));
    }
}
