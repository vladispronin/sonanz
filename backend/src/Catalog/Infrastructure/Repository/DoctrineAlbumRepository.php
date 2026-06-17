<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Repository;

use App\Catalog\Domain\Entity\Album;
use App\Catalog\Domain\Port\AlbumRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineAlbumRepository extends ServiceEntityRepository implements AlbumRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Album::class);
    }

    public function create(string $jobId): Album
    {
        $album = new Album(jobId: $jobId);

        $this->getEntityManager()->persist($album);
        $this->getEntityManager()->flush();

        return $album;
    }
}
