<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

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

    #[ORM\Column(length: 255)]
    private string $jobId;

    #[ORM\Column(length: 255)]
    private string $url;

    #[ORM\ManyToOne(targetEntity: Album::class, inversedBy: 'tracks')]
    private ?Album $album;

    #[ORM\Column]
    private bool $isDownloaded = false;

    public function __construct(string $jobId, string $url, ?Album $album = null)
    {
        $this->id = Uuid::v7();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        $this->jobId = $jobId;
        $this->url = $url;
        $this->album = $album;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }
}
