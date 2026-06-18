<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Port;

use App\Catalog\Domain\Entity\Job;
use App\Shared\Domain\Enum\TitleTypeEnum;
use Symfony\Component\Uid\Uuid;

interface JobRepositoryInterface
{
    public function create(
        string $author,
        string $title,
        TitleTypeEnum $titleType,
        ?Uuid $id = null,
    ): void;

    public function getJobObject(Uuid $jobId): Job;

    public function updateProgress(Uuid $jobId, int $progress): void;

    public function start(Uuid $jobId): void;

    public function complete(Uuid $jobId): void;

    public function cancel(Uuid $jobId): void;

    public function fail(Uuid $jobId): void;
}
