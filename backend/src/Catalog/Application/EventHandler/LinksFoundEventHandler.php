<?php

declare(strict_types=1);

namespace App\Catalog\Application\EventHandler;

use App\Catalog\Domain\Port\AlbumRepositoryInterface;
use App\Catalog\Domain\Port\JobRepositoryInterface;
use App\Catalog\Domain\Port\TrackRepositoryInterface;
use App\Catalog\Domain\ValueObject\JobProgress;
use App\Shared\Application\Event\LinksFoundEvent;
use App\Shared\Application\Event\TrackCreatedEvent;
use App\Shared\Domain\Enum\TitleTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class LinksFoundEventHandler
{
    public function __construct(
        private TrackRepositoryInterface $trackRepository,
        private AlbumRepositoryInterface $albumRepository,
        private JobRepositoryInterface $jobRepository,
        private MessageBusInterface $bus,
        private EntityManagerInterface $entityManager,
    ) {}

    public function __invoke(LinksFoundEvent $event): void
    {
        $this->jobRepository->updateProgress($event->jobId, JobProgress::LINKS_FOUND);

        if ($event->titleType === TitleTypeEnum::Track) {
            $track = $this->trackRepository->create(
                jobId: $event->jobId,
                url: $event->audioSourceLinks[0]->url,
                title: $event->audioSourceLinks[0]->title,
                album: null
            );

            $this->bus->dispatch(new TrackCreatedEvent(
                trackId: $track->getId(),
                jobId: $event->jobId,
                url: $event->audioSourceLinks[0]->url,
            ));
        }

        if ($event->titleType === TitleTypeEnum::Album) {
            $album = null;
            $tracks = [];

            $this->entityManager->wrapInTransaction(function () use ($event, &$album, &$tracks): void {
                $album = $this->albumRepository->create($event->jobId, $event->title);

                foreach ($event->audioSourceLinks as $link) {
                    $tracks[] = $this->trackRepository->create(
                        jobId: $event->jobId,
                        url: $link->url,
                        title: $link->title,
                        album: $album
                    );
                }
            });

            foreach ($tracks as $track) {
                $this->bus->dispatch(new TrackCreatedEvent(
                    trackId: $track->getId(),
                    jobId: $event->jobId,
                    url: $track->getUrl(),
                    albumId: $album->getId(),
                ));
            }
        }
    }
}
