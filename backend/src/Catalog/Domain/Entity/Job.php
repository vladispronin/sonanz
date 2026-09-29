<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\Enum\JobStatusEnum;
use App\Catalog\Domain\ValueObject\JobProgress;
use App\Shared\Domain\Enum\TitleTypeEnum;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'jobs')]
class Job
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    public Uuid $id {
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

    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $userId;

    #[ORM\Column]
    private int $progress = JobProgress::JOB_INITIATED;

    #[ORM\Column(length: 255)]
    private string $author;

    #[ORM\Column(length: 255)]
    public string $title;

    #[ORM\Column(length: 255)]
    public TitleTypeEnum $titleType;

    #[ORM\Column(options: ['default' => false])]
    public bool $withMetadata = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $metadataAuthor = null;

    public function __construct(
        string $author,
        Uuid $userId,
        string $title,
        TitleTypeEnum $titleType,
        ?Uuid $id = null,
        bool $withMetadata = false,
    )
    {
        $this->id = $id ?? Uuid::v7();

        $this->author = $author;
        $this->userId = $userId;
        $this->title = $title;
        $this->titleType = $titleType;
        $this->withMetadata = $withMetadata;

        $this->status = JobStatusEnum::Pending;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getTitleType(): TitleTypeEnum
    {
        return $this->titleType;
    }

    public function getStatus(): JobStatusEnum
    {
        return $this->status;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function enrichWithMetadataAuthor(string $author): void
    {
        $this->metadataAuthor = $author;
    }

    public function getActualAuthor(): string
    {
        return $this->metadataAuthor ?? $this->author;
    }

    public function markAsProcessing(): void
    {
        $this->status = JobStatusEnum::Processing;
    }

    public function markAsCompleted(): void
    {
        $this->status = JobStatusEnum::Completed;
    }

    public function markAsCancelled(): void
    {
        $this->status = JobStatusEnum::Cancelled;
    }

    public function markAsFailed(): void
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
