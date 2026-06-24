<?php

declare(strict_types=1);

namespace App\Download\Application\EventHandler;

use App\Download\Domain\Port\AudioDownloadProviderInterface;
use App\Download\Domain\ValueObject\AudioDownloadQuery;
use App\Shared\Application\Event\AudioDownloadedEvent;
use App\Shared\Application\Event\TrackCreatedEvent;
use App\Shared\Application\Event\TrackFailedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
class TrackCreatedEventHandler
{
    private AudioDownloadProviderInterface $audioDownloadProvider;
    private MessageBusInterface $messageBus;
    private LoggerInterface $logger;

    public function __construct(
        AudioDownloadProviderInterface $audioDownloadProvider,
        MessageBusInterface $messageBus,
        LoggerInterface $logger,
    ) {
        $this->audioDownloadProvider = $audioDownloadProvider;
        $this->messageBus = $messageBus;
        $this->logger = $logger;
    }

    public function __invoke(TrackCreatedEvent $event): void
    {
        $audioDownloadQuery = new AudioDownloadQuery($event->url, $event->trackId);

        try {
            $this->audioDownloadProvider->download($audioDownloadQuery);
            $this->messageBus->dispatch(new AudioDownloadedEvent($event->trackId, $event->jobId, $event->albumId));
        } catch (\Throwable $e) {
            $this->logger->warning('Скачивание трека не удалось: ' . $e->getMessage(), [
                'track_id' => $event->trackId->toString(),
                'url'      => $event->url,
            ]);
            $this->messageBus->dispatch(new TrackFailedEvent($event->trackId, $event->jobId, $event->albumId));
        }
    }
}
