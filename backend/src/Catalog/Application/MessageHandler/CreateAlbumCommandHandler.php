<?php

declare(strict_types=1);

namespace App\Catalog\Application\MessageHandler;

use App\Catalog\Application\Event\TrackCreatedEvent;
use App\Catalog\Application\Message\CreateAlbumCommand;
use App\Catalog\Domain\Port\AlbumRepositoryInterface;
use App\Catalog\Domain\Port\TrackRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class CreateAlbumCommandHandler
{
    public function __construct(
        private AlbumRepositoryInterface $albumRepository,
        private TrackRepositoryInterface $trackRepository,
        private MessageBusInterface $bus,
    ) {}

    public function __invoke(CreateAlbumCommand $command): void
    {
        $album = $this->albumRepository->create($command->jobId);

        foreach ($command->trackUrls as $url) {
            $track = $this->trackRepository->create($command->jobId, $url, $album);

            $this->bus->dispatch(new TrackCreatedEvent(
                trackId: $track->getId()->toString(),
                jobId: $command->jobId,
                url: $url,
                albumId: $album->getId()->toString(),
            ));
        }
    }
}
