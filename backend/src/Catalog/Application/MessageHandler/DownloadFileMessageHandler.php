<?php

declare(strict_types=1);

namespace App\Catalog\Application\MessageHandler;

use App\Catalog\Domain\Entity\Job;
use App\Catalog\Domain\Port\AlbumRepositoryInterface;
use App\Catalog\Domain\Port\JobRepositoryInterface;
use App\Catalog\Domain\Port\TrackRepositoryInterface;
use App\Shared\Application\Dto\DownloadFileDto;
use App\Shared\Application\Message\DownloadFileMessage;
use App\Shared\Domain\Enum\TitleTypeEnum;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
class DownloadFileMessageHandler
{
    private JobRepositoryInterface $jobRepository;
    private TrackRepositoryInterface $trackRepository;
    private AlbumRepositoryInterface $albumRepository;

    public function __construct(
        JobRepositoryInterface $jobRepository,
        TrackRepositoryInterface $trackRepository,
        AlbumRepositoryInterface $albumRepository
    )
    {
        $this->jobRepository = $jobRepository;
        $this->trackRepository = $trackRepository;
        $this->albumRepository = $albumRepository;
    }

    public function __invoke(DownloadFileMessage $message): DownloadFileDto
    {
        $job = $this->jobRepository->find($message->jobId);

        $mediaType = $job->getTitleType();
        $mediaId = $this->getMediaId($mediaType, $message->jobId);

        return new DownloadFileDto(
            path: '/tmp/' . $mediaId->toString() . '.' . $mediaType->fileExtension(),
            mediaName: $this->getMediaName($mediaType, $job),
        );
    }

    private function getMediaId(TitleTypeEnum $mediaType, Uuid $jobId): Uuid
    {
        return match ($mediaType) {
            TitleTypeEnum::Track => $this->trackRepository->findOneBy(['jobId' => $jobId])->getId(),
            TitleTypeEnum::Album => $this->albumRepository->findOneBy(['jobId' => $jobId])->getId(),
        };
    }

    private function getMediaName(TitleTypeEnum $mediaType, Job $job): string
    {
        if ($mediaType === TitleTypeEnum::Track) {
            $track = $this->trackRepository->findOneBy(['jobId' => $job->getId()]);
            $title = $track->getMetadataAuthor() ? $track->getTitle() : $job->getTitle();
            $author = $track->getMetadataAuthor() ?? $job->getAuthor();
        } elseif ($mediaType === TitleTypeEnum::Album) {
            $album = $this->albumRepository->findOneBy(['jobId' => $job->getId()]);
            $title = $job->getActualAuthor() ? $album->getTitle() : $job->getTitle();
            $author = $job->getActualAuthor() ?? $job->getAuthor();
        }

        return isset($title) && isset($author) ? $author . ' — ' . $title : $job->getId()->toString();
    }
}
