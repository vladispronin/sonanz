<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Repository;

use App\Catalog\Domain\Entity\Job;
use App\Catalog\Domain\Port\JobRepositoryInterface;
use App\Shared\Domain\Enum\TitleTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class DoctrineJobRepository extends ServiceEntityRepository implements JobRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Job::class);
    }

    public function create(
        string $author,
        string $title,
        TitleTypeEnum $titleType,
        ?Uuid $id = null
    ): void
    {
        $job = new Job(
            $author,
            $title,
            $titleType,
            $id
        );

        $this->getEntityManager()->persist($job);
        $this->getEntityManager()->flush();
    }

    public function updateProgress(Uuid $jobId, int $progress): void
    {
        $job = $this->find($jobId);
        $job->updateProgress($progress);
        $this->getEntityManager()->flush();
    }

    public function start(Uuid $jobId): void
    {
        $job = $this->find($jobId);
        $job->start();
        $this->getEntityManager()->flush();
    }

    public function complete(Uuid $jobId): void
    {
        $job = $this->find($jobId);
        $job->complete();
        $this->getEntityManager()->flush();
    }

    public function cancel(Uuid $jobId): void
    {
        $job = $this->find($jobId);
        $job->cancel();
        $this->getEntityManager()->flush();
    }

    public function fail(Uuid $jobId): void
    {
        $job = $this->find($jobId);
        $job->fail();
        $this->getEntityManager()->flush();
    }

    public function getJobObject(Uuid $jobId): Job
    {
        return $this->find($jobId);
    }
}
