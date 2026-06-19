<?php

declare(strict_types=1);

namespace App\Catalog\Application\EventHandler;

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
    private EntityManagerInterface $entityManager;
    private MessageBusInterface $messageBus;

    public function __construct(
        TrackRepositoryInterface $trackRepository,
        JobRepositoryInterface $jobRepository,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus
    ) {
        $this->trackRepository = $trackRepository;
        $this->jobRepository = $jobRepository;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
    }
    public function __invoke(AudioDownloadedEvent $event): void
    {
        $this->trackRepository->markAsDownloaded($event->trackId);

        if ($this->isMetadataNeeded($event->jobId)) {
            $this->messageBus->dispatch(new TagAudioMessage($event->trackId));
        } else {
            $this->trackRepository->markAsCompleted($event->trackId);

            if (!$event->albumId) {
                $this->jobRepository->complete($event->jobId);
            } else {
                if ($this->trackRepository->allTracksCompleted($event->albumId)) {
                    $tracks = $this->trackRepository->getAlbumTracksData($event->albumId);
                    $this->messageBus->dispatch(new ArchiveAlbumMessage($event->albumId, $tracks));
                }
            }
        }
    }

    private function isMetadataNeeded(Uuid $jobId): bool
    {
        return $this->jobRepository->getJobObject($jobId)->withMetadata;
    }
}
