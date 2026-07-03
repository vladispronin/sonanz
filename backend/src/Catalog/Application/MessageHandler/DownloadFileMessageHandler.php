<?php

declare(strict_types=1);

namespace App\Catalog\Application\MessageHandler;

use App\Catalog\Domain\Port\AlbumRepositoryInterface;
use App\Catalog\Domain\Port\JobRepositoryInterface;
use App\Catalog\Domain\Port\TrackRepositoryInterface;
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

    public function __invoke(DownloadFileMessage $message): string
    {
        $job = $this->jobRepository->find($message->jobId);

        $mediaType = $job->getTitleType();

        return '/tmp/' . $this->getMediaId($mediaType, $message->jobId)->toString() . '.' . $mediaType->fileExtension();
    }

    private function getMediaId(TitleTypeEnum $mediaType, Uuid $jobId): Uuid
    {
        return match ($mediaType) {
            TitleTypeEnum::Track => $this->trackRepository->findOneBy(['jobId' => $jobId])->getId(),
            TitleTypeEnum::Album => $this->albumRepository->findOneBy(['jobId' => $jobId])->getId(),
        };
    }
}
