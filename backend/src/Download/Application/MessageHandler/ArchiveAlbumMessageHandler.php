<?php

declare(strict_types=1);

namespace App\Download\Application\MessageHandler;

use App\Shared\Application\Event\AlbumArchivedEvent;
use App\Shared\Application\Message\ArchiveAlbumMessage;
use App\Shared\Domain\ValueObject\TrackArchiveEntry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class ArchiveAlbumMessageHandler
{
    private MessageBusInterface $messageBus;
    private LoggerInterface $logger;

    public function __construct(
        MessageBusInterface $messageBus,
        LoggerInterface $logger
    ) {
        $this->messageBus = $messageBus;
        $this->logger = $logger;
    }

    public function __invoke(ArchiveAlbumMessage $message): void
    {
        $this->archive($message);

        $this->messageBus->dispatch(new AlbumArchivedEvent($message->jobId));
    }

    private function archive(ArchiveAlbumMessage $message): void
    {
        $zip = new \ZipArchive();
        $archivePath = '/tmp/' . $message->albumId->toString() . '.zip';

        $zip->open($archivePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $addedFiles = [];
        foreach ($message->tracks as $track) {
            $filePath = '/tmp/' . $track->id . '.mp3';
            if (!file_exists($filePath)) {
                $this->logger->warning('Файл трека не найден при архивации', ['path' => $filePath]);
                continue;
            }
            $zip->addFile($filePath, $track->author . ' — ' . $track->title . '.mp3');
            $addedFiles[] = $filePath;
        }

        $zip->close();

        foreach ($addedFiles as $filePath) {
            unlink($filePath);
        }
    }
}
