<?php

declare(strict_types=1);

namespace App\Catalog\Application\EventHandler;

use App\Catalog\Domain\Port\AlbumRepositoryInterface;
use App\Catalog\Domain\Port\JobRepositoryInterface;
use App\Catalog\Domain\Port\TrackRepositoryInterface;
use App\Catalog\Domain\ValueObject\JobProgress;
use App\Shared\Application\Event\TrackFailedEvent;
use App\Shared\Application\Message\ArchiveAlbumMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class TrackFailedEventHandler
{
    public function __construct(
        private TrackRepositoryInterface $trackRepository,
        private JobRepositoryInterface $jobRepository,
        private AlbumRepositoryInterface $albumRepository,
        private MessageBusInterface $messageBus,
    ) {}

    public function __invoke(TrackFailedEvent $event): void
    {
        $this->trackRepository->markAsFailed($event->trackId);

        if ($event->albumId === null) {
            $this->jobRepository->fail($event->jobId);
            return;
        }

        if (!$this->trackRepository->allTracksHandled($event->albumId)) {
            return;
        }

        $artist = $this->jobRepository->findById($event->jobId)->getActualAuthor();
        $albumTitle = $this->albumRepository->getTitleById($event->albumId);
        $tracks = $this->trackRepository->getAlbumTracksData($event->albumId);

        $this->jobRepository->updateProgress($event->jobId, JobProgress::MEDIA_DOWNLOADED);

        $this->messageBus->dispatch(new ArchiveAlbumMessage($artist, $albumTitle, $tracks, $event->jobId));
    }
}
