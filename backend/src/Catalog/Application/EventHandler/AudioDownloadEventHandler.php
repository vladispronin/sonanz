<?php

declare(strict_types=1);

namespace App\Catalog\Application\EventHandler;

use App\Catalog\Domain\Port\AlbumRepositoryInterface;
use App\Catalog\Domain\Port\JobRepositoryInterface;
use App\Catalog\Domain\Port\TrackRepositoryInterface;
use App\Shared\Application\Event\AudioDownloadedEvent;
use App\Shared\Application\Message\ArchiveAlbumMessage;
use App\Shared\Application\Message\TagAudioMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
class AudioDownloadEventHandler
{
    private TrackRepositoryInterface $trackRepository;
    private JobRepositoryInterface $jobRepository;
    private AlbumRepositoryInterface $albumRepository;
    private EntityManagerInterface $entityManager;
    private MessageBusInterface $messageBus;

    public function __construct(
        TrackRepositoryInterface $trackRepository,
        JobRepositoryInterface $jobRepository,
        AlbumRepositoryInterface $albumRepository,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus
    ) {
        $this->trackRepository = $trackRepository;
        $this->jobRepository = $jobRepository;
        $this->albumRepository = $albumRepository;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
    }
    public function __invoke(AudioDownloadedEvent $event): void
    {
        $this->trackRepository->markAsDownloaded($event->trackId);

        if ($this->isMetadataNeeded($event->jobId)) {
            $authorTitle = $this->jobRepository->findById($event->jobId)->getAuthor();
            $trackTitle = $this->trackRepository->findById($event->trackId)->getTitle();
            $this->messageBus->dispatch(new TagAudioMessage($event->trackId, $authorTitle, $trackTitle));
        } else {
            $this->trackRepository->markAsCompleted($event->trackId);

            if (is_null($event->albumId)) {
                $this->jobRepository->complete($event->jobId);
            } else {
                if ($this->trackRepository->allTracksCompleted($event->albumId)) {
                    $artist = $this->jobRepository->findById($event->jobId)->getActualAuthor();
                    $albumTitle = $this->albumRepository->getTitleById($event->albumId);
                    $tracks = $this->trackRepository->getAlbumTracksData($event->albumId);
                    $this->messageBus->dispatch(new ArchiveAlbumMessage($artist, $albumTitle, $tracks));
                }
            }
        }
    }

    private function isMetadataNeeded(Uuid $jobId): bool
    {
        return $this->jobRepository->findById($jobId)->withMetadata;
    }
}
