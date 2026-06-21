<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Repository;

use App\Catalog\Domain\Entity\Job;
use App\Catalog\Domain\Port\JobRepositoryInterface;
use App\Catalog\Domain\ValueObject\JobProgress;
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
        ?Uuid $id = null,
        bool $withMetadata = false,
    ): void
    {
        $job = new Job(
            $author,
            $title,
            $titleType,
            $id,
            $withMetadata,
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
        $job->markAsProcessing();
        $this->getEntityManager()->flush();
    }

    public function complete(Uuid $jobId): void
    {
        $this->getEntityManager()->wrapInTransaction(function () use ($jobId): void {
            $job = $this->find($jobId);
            $job->markAsCompleted();
            $job->updateProgress(JobProgress::JOB_COMPLETED);
            $this->getEntityManager()->flush();
        });
    }

    public function cancel(Uuid $jobId): void
    {
        $job = $this->find($jobId);
        $job->markAsCancelled();
        $this->getEntityManager()->flush();
    }

    public function fail(Uuid $jobId): void
    {
        $job = $this->find($jobId);
        $job->markAsFailed();
        $this->getEntityManager()->flush();
    }

    public function findById(Uuid $jobId): Job
    {
        return $this->find($jobId);
    }

    public function enrichWithMetadataAuthor(Uuid $jobId, string $author): void
    {
        $job = $this->find($jobId);
        $job->enrichWithMetadataAuthor($author);
        $this->getEntityManager()->flush();
    }
}
