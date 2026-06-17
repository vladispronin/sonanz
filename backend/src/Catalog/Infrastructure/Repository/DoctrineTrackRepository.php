<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Repository;

use App\Catalog\Domain\Entity\Album;
use App\Catalog\Domain\Entity\Track;
use App\Catalog\Domain\Port\TrackRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineTrackRepository extends ServiceEntityRepository implements TrackRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Track::class);
    }

    public function create(string $jobId, string $url, ?Album $album): Track
    {
        $track = new Track(
            jobId: $jobId,
            url: $url,
            album: $album,
        );

        $this->getEntityManager()->persist($track);
        $this->getEntityManager()->flush();

        return $track;
    }
}
