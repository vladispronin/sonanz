<?php

declare(strict_types=1);

namespace App\Catalog\Application\EventHandler;

use App\Catalog\Domain\Port\AlbumRepositoryInterface;
use App\Catalog\Domain\Port\JobRepositoryInterface;
use App\Catalog\Domain\Port\TrackRepositoryInterface;
use App\Catalog\Domain\ValueObject\JobProgress;
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

        if ($event->metadata?->artist !== null) {
            $this->trackRepository->enrichWithMetadataAuthor($event->trackId, $event->metadata->artist);
        }

        $track = $this->trackRepository->findById($event->trackId);
        $album = $track->getAlbum();
        $jobId = $track->getJobId();

        if ($album === null) {
            $this->trackRepository->markAsCompleted($event->trackId);

            $this->jobRepository->updateProgress($jobId, JobProgress::JOB_COMPLETED);
            $this->jobRepository->complete($jobId);
            return;
        }

        $this->trackRepository->markAsCompleted($event->trackId);

        if (!$this->trackRepository->allTracksHandled($album->getId())) {
            return;
        }

        if ($event->metadata?->albumTitle !== null) {
            $this->albumRepository->updateTitle($album->getId(), $event->metadata->albumTitle);
        }

        if ($event->metadata?->artist !== null) {
            $this->jobRepository->enrichWithMetadataAuthor($jobId, $event->metadata->albumArtist);
        }

        $job = $this->jobRepository->findById($jobId);
        $tracks = $this->trackRepository->getAlbumTracksData($album->getId());

        $this->jobRepository->updateProgress($jobId, JobProgress::MEDIA_TAGGED);

        $this->messageBus->dispatch(new ArchiveAlbumMessage($job->getActualAuthor(), $album->getTitle(), $tracks, $jobId));
    }
}
