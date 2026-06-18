<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\Enum\JobStatusEnum;
use App\Shared\Domain\Enum\TitleTypeEnum;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

class Job
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private Uuid $id {
        get {
            return $this->id;
        }
    }

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column]
    private DateTimeImmutable $updatedAt;

    #[ORM\Column(length: 255)]
    private JobStatusEnum $status;

    #[ORM\Column]
    private int $progress = 0;

    #[ORM\Column(length: 255)]
    private string $author {
        get {
            return $this->author;
        }
    }

    #[ORM\Column(length: 255)]
    private string $title {
        get {
            return $this->title;
        }
    }

    #[ORM\Column(length: 255)]
    private TitleTypeEnum $titleType {
        get {
            return $this->titleType;
        }
    }

    public function __construct(
        string $author,
        string $title,
        TitleTypeEnum $titleType,
        ?Uuid $id = null
    )
    {
        $this->id = $id ?? Uuid::v7();

        $this->author = $author;
        $this->title = $title;
        $this->titleType = $titleType;

        $this->status = JobStatusEnum::Pending;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function start(): void
    {
        $this->status = JobStatusEnum::Processing;
    }

    public function complete(): void
    {
        $this->status = JobStatusEnum::Completed;
    }

    public function cancel(): void
    {
        $this->status = JobStatusEnum::Cancelled;
    }

    public function fail(): void
    {
        $this->status = JobStatusEnum::Failed;
    }

    public function updateProgress(int $progress): void
    {
        if ($this->validate($progress)) {
            $this->progress = $progress;
        }
    }

    private function validate(int $progress): bool
    {
        return $progress >= 0 && $progress <= 100;
    }
}
