<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\Enum\TrackStatusEnum;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'tracks')]
class Track
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private Uuid $id;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column]
    private DateTimeImmutable $updatedAt;

    #[ORM\Column(type: UuidType::NAME, length: 255)]
    private Uuid $jobId;

    #[ORM\Column(length: 255)]
    private string $url;

    #[ORM\ManyToOne(targetEntity: Album::class, inversedBy: 'tracks')]
    private ?Album $album;

    #[ORM\Column(length: 255)]
    private TrackStatusEnum $status;

    #[ORM\Column(length: 255)]
    private string $title;

    public function __construct(
        Uuid $jobId,
        string $url,
        string $title,
        ?Album $album = null,
    )
    {
        $this->id = Uuid::v7();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        $this->jobId = $jobId;
        $this->url = $url;
        $this->title = $title;
        $this->album = $album;
        $this->status = TrackStatusEnum::Created;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function markAsDownloaded(): void
    {
        $this->status = TrackStatusEnum::Downloaded;
    }

    public function markAsTagged(): void
    {
        $this->status = TrackStatusEnum::Tagged;
    }

    public function markAsCompleted(): void
    {
        $this->status = TrackStatusEnum::Completed;
    }

    public function markAsFailed(): void
    {
        $this->status = TrackStatusEnum::Failed;
    }
}
