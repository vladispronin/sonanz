<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Port;

use App\Catalog\Domain\Entity\Job;
use Symfony\Component\Uid\Uuid;

interface JobRepositoryInterface
{
    public function create(?Uuid $id = null): Job;

    public function updateProgress(string $jobId, int $progress): void;
}
