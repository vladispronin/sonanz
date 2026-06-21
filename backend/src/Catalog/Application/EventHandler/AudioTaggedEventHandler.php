<?php

declare(strict_types=1);

namespace App\Catalog\Application\EventHandler;

use App\Catalog\Domain\Port\JobRepositoryInterface;
use App\Catalog\Domain\Port\TrackRepositoryInterface;
use App\Shared\Application\Event\AudioTaggedEvent;
use App\Shared\Application\Message\ArchiveAlbumMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class AudioTaggedEventHandler
{
    private TrackRepositoryInterface $trackRepository;
    private JobRepositoryInterface $jobRepository;
    private MessageBusInterface $messageBus;

    public function __construct(
        TrackRepositoryInterface $trackRepository,
        JobRepositoryInterface $jobRepository,
        MessageBusInterface $messageBus,
    )
    {
        $this->trackRepository = $trackRepository;
        $this->jobRepository = $jobRepository;
        $this->messageBus = $messageBus;
    }
    public function __invoke(AudioTaggedEvent $event): void
    {
        $this->trackRepository->markAsTagged($event->trackId);

        $track = $this->trackRepository->findById($event->trackId);

        $album = $track->getAlbum();

        $jobId = $track->getJobId();

        if (is_null($album)) {
            $this->trackRepository->markAsCompleted($event->trackId);
            $this->jobRepository->complete($jobId);
        } else {
            if ($this->trackRepository->allTracksCompleted($album->getId())) {
                $artist = $this->jobRepository->findById($jobId)->getAuthor();
                $albumTitle = $album->getTitle();
                $tracks = $this->trackRepository->getAlbumTracksData($album->getId());
                $this->messageBus->dispatch(new ArchiveAlbumMessage($artist, $albumTitle, $tracks));
            }
        }
    }
}
