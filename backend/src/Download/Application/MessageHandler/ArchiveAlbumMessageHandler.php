<?php

declare(strict_types=1);

namespace App\Download\Application\MessageHandler;

use App\Shared\Application\Message\ArchiveAlbumMessage;
use App\Shared\Domain\ValueObject\TrackArchiveEntry;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ArchiveAlbumMessageHandler
{
    public function __invoke(ArchiveAlbumMessage $message): void
    {
        $this->archive($message->artist, $message->albumTitle, $message->tracks);
    }

    /**
     * @param TrackArchiveEntry[] $tracks
     */
    private function archive(string $authorName, string $albumTitle, array $tracks): void
    {
        $zip = new \ZipArchive();
        $archivePath = '/tmp/' . $authorName . ' — ' . $albumTitle . '.zip';

        $zip->open($archivePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($tracks as $track) {
            $filePath = '/tmp/' . $track->id . '.mp3';
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $authorName . ' — ' .$track->title . '.mp3');
            }
        }

        $zip->close();
    }
}
