<?php

namespace App\Entity;

use App\Repository\ReportRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReportRepository::class)]
class Report
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'reportsUser')]
    #[ORM\JoinColumn(nullable: false)]
    private ?History $uuid = null;

    #[ORM\ManyToOne(inversedBy: 'reportsMovie')]
    #[ORM\JoinColumn(nullable: false)]
    private ?History $tmdb = null;

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUuid(): ?History
    {
        return $this->uuid;
    }

    public function setUuid(?History $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getTmdb(): ?History
    {
        return $this->tmdb;
    }

    public function setTmdb(?History $tmdb): static
    {
        $this->tmdb = $tmdb;

        return $this;
    }


}
