<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Repository;

use App\Catalog\Domain\Entity\Album;
use App\Catalog\Domain\Port\AlbumRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class DoctrineAlbumRepository extends ServiceEntityRepository implements AlbumRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Album::class);
    }

    public function create(Uuid $jobId, string $title): Album
    {
        $album = new Album(jobId: $jobId, title: $title);

        $this->getEntityManager()->persist($album);
        $this->getEntityManager()->flush();

        return $album;
    }

    public function getTitleById(Uuid $albumId): string
    {
        return $this->find($albumId)->getTitle();
    }

    public function updateTitle(Uuid $albumId, string $title): void
    {
        $album = $this->find($albumId);
        $album->updateTitle($title);
        $this->getEntityManager()->flush();
    }
}
