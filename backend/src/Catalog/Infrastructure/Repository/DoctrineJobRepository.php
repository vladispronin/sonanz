<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Repository;

use App\Catalog\Domain\Entity\Job;
use App\Catalog\Domain\Port\JobRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class DoctrineJobRepository extends ServiceEntityRepository implements JobRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Job::class);
    }

    public function updateProgress(string $jobId, int $progress): void
    {
        $job = $this->find($jobId);
        $job->updateProgress($progress);
        $this->getEntityManager()->flush();
    }

    public function create(?Uuid $id = null): Job
    {
        $job = new Job($id);

        $this->getEntityManager()->persist($job);
        $this->getEntityManager()->flush();

        return $job;
    }
}
