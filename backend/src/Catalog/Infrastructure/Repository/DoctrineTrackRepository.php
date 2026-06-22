<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Repository;

use App\Catalog\Domain\Entity\Album;
use App\Catalog\Domain\Entity\Track;
use App\Catalog\Domain\Enum\TrackStatusEnum;
use App\Catalog\Domain\Port\TrackRepositoryInterface;
use App\Shared\Domain\ValueObject\TrackArchiveEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class DoctrineTrackRepository extends ServiceEntityRepository implements TrackRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Track::class);
    }

    public function create(Uuid $jobId, string $url, string $title, ?Album $album): Track
    {
        $track = new Track(
            jobId: $jobId,
            url: $url,
            title: $title,
            album: $album,
        );

        $this->getEntityManager()->persist($track);
        $this->getEntityManager()->flush();

        return $track;
    }

    public function findById(Uuid $trackId): Track
    {
        return $this->find($trackId);
    }

    public function markAsDownloaded(Uuid $trackId): void
    {
        $track = $this->find($trackId);
        $track->markAsDownloaded();
        $this->getEntityManager()->flush();
    }

    public function markAsTagged(Uuid $trackId): void
    {
        $track = $this->find($trackId);
        $track->markAsTagged();
        $this->getEntityManager()->flush();
    }

    public function markAsCompleted(Uuid $trackId): void
    {
        $track = $this->find($trackId);
        $track->markAsCompleted();
        $this->getEntityManager()->flush();
    }

    public function markAsFailed(Uuid $trackId): void
    {
        $track = $this->find($trackId);
        $track->markAsFailed();
        $this->getEntityManager()->flush();
    }

    /**
     * @return TrackArchiveEntry[]
     */
    public function getAlbumTracksData(Uuid $albumId): array
    {
        $tracks = $this->findBy(['album' => $albumId]);

        return array_map(
            fn(Track $track) => new TrackArchiveEntry(
                id: $track->getId(),
                author: $track->getMetadataAuthor(),
                title: $track->getTitle(),
            ),
            $tracks,
        );
    }

    public function updateTitle(Uuid $trackId, string $title): void
    {
        $track = $this->find($trackId);
        $track->updateTitle($title);
        $this->getEntityManager()->flush();
    }

    public function enrichWithMetadataAuthor(Uuid $trackId, string $author): void
    {
        $track = $this->find($trackId);
        $track->enrichWithMetadataAuthor($author);
        $this->getEntityManager()->flush();
    }

    public function allTracksCompleted(Uuid $albumId): bool
    {
        $count = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.album = :albumId')
            ->andWhere('t.status != :status')
            ->setParameter('albumId', $albumId)
            ->setParameter('status', TrackStatusEnum::Completed)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count === 0;
    }
}
