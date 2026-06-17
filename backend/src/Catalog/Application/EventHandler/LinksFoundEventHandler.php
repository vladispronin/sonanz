<?php

declare(strict_types=1);

namespace App\Catalog\Application\EventHandler;

use App\Catalog\Application\Event\TrackCreatedEvent;
use App\Catalog\Domain\Port\AlbumRepositoryInterface;
use App\Catalog\Domain\Port\JobRepositoryInterface;
use App\Catalog\Domain\Port\TrackRepositoryInterface;
use App\Catalog\Domain\ValueObject\JobProgress;
use App\Link\Application\Event\LinksFoundEvent;
use App\Link\Domain\Enum\TitleTypeEnum;
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
    ) {}

    public function __invoke(LinksFoundEvent $event): void
    {
        $this->jobRepository->updateProgress($event->jobId, JobProgress::LINKS_FOUND);

        if ($event->titleType === TitleTypeEnum::Track) {
            $track = $this->trackRepository->create($event->jobId, $event->urls[0], null);

            $this->bus->dispatch(new TrackCreatedEvent(
                trackId: $track->getId()->toString(),
                jobId: $event->jobId,
                url: $event->urls[0],
            ));
        }

        if ($event->titleType === TitleTypeEnum::Album) {
            $album = $this->albumRepository->create($event->jobId);

            foreach ($event->urls as $url) {
                $track = $this->trackRepository->create($event->jobId, $url, $album);

                $this->bus->dispatch(new TrackCreatedEvent(
                    trackId: $track->getId()->toString(),
                    jobId: $event->jobId,
                    url: $url,
                    albumId: $album->getId()->toString(),
                ));
            }
        }
    }
}
