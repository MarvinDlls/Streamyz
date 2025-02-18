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

    #[ORM\ManyToOne(inversedBy: 'reports')]
    private ?History $uuid = null;

    public function getId(): ?int
    {
        return $this->id;
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


}
