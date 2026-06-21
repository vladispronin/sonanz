<?php

declare(strict_types=1);

namespace App\Catalog\Application\EventHandler;

use App\Catalog\Domain\Port\AlbumRepositoryInterface;
use App\Catalog\Domain\Port\JobRepositoryInterface;
use App\Catalog\Domain\Port\TrackRepositoryInterface;
use App\Shared\Application\Event\AudioTaggedEvent;
use App\Shared\Application\Message\ArchiveAlbumMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class AudioTaggedEventHandler
{
    public function __construct(
        private TrackRepositoryInterface $trackRepository,
        private AlbumRepositoryInterface $albumRepository,
        private JobRepositoryInterface $jobRepository,
        private MessageBusInterface $messageBus,
    ) {}

    public function __invoke(AudioTaggedEvent $event): void
    {
        $this->trackRepository->markAsTagged($event->trackId);

        if ($event->metadata?->trackTitle !== null) {
            $this->trackRepository->updateTitle($event->trackId, $event->metadata->trackTitle);
        }

        $track = $this->trackRepository->findById($event->trackId);
        $album = $track->getAlbum();
        $jobId = $track->getJobId();

        if ($album === null) {
            $this->trackRepository->markAsCompleted($event->trackId);
            $this->jobRepository->complete($jobId);
            return;
        }

        $this->trackRepository->markAsCompleted($event->trackId);

        if (!$this->trackRepository->allTracksCompleted($album->getId())) {
            return;
        }

        if ($event->metadata?->albumTitle !== null) {
            $this->albumRepository->updateTitle($album->getId(), $event->metadata->albumTitle);
        }

        if ($event->metadata?->artist !== null) {
            $this->jobRepository->enrichWithMetadataAuthor($jobId, $event->metadata->artist);
        }

        $job = $this->jobRepository->findById($jobId);
        $tracks = $this->trackRepository->getAlbumTracksData($album->getId());

        $this->messageBus->dispatch(new ArchiveAlbumMessage($job->getActualAuthor(), $album->getTitle(), $tracks));
    }
}
