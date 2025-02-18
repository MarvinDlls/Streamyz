<?php

namespace App\Entity;

use App\Repository\HistoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Nullable;

#[ORM\Entity(repositoryClass: HistoryRepository::class)]
class History
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column]
    private ?bool $is_watched = null;

    #[ORM\Column(length: 255)]
    private ?string $ip_adress = null;

    /**
     * @var Collection<int, Report>
     */
    #[ORM\OneToMany(targetEntity: Report::class, mappedBy: 'uuid', orphanRemoval: true)]
    private Collection $reportsUser;

    /**
     * @var Collection<int, Report>
     */
    #[ORM\OneToMany(targetEntity: Report::class, mappedBy: 'tmdb', orphanRemoval: true)]
    private Collection $reportsMovie;


    public function __construct()
    {
        $this->is_watched = false;
        $this->reportsUser = new ArrayCollection();
        $this->reportsMovie = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updated_at = new \DateTimeImmutable();
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function isWatched(): ?bool
    {
        return $this->is_watched;
    }

    public function setIsWatched(bool $is_watched): static
    {
        $this->is_watched = $is_watched;

        return $this;
    }

    public function getIpAdress(): ?string
    {
        return $this->ip_adress;
    }

    public function setIpAdress(string $ip_adress): static
    {
        $this->ip_adress = $ip_adress;

        return $this;
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReportsUser(): Collection
    {
        return $this->reportsUser;
    }

    public function addReportsUser(Report $reportsUser): static
    {
        if (!$this->reportsUser->contains($reportsUser)) {
            $this->reportsUser->add($reportsUser);
            $reportsUser->setUuid($this);
        }

        return $this;
    }

    public function removeReportsUser(Report $reportsUser): static
    {
        if ($this->reportsUser->removeElement($reportsUser)) {
            // set the owning side to null (unless already changed)
            if ($reportsUser->getUuid() === $this) {
                $reportsUser->setUuid(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReportsMovie(): Collection
    {
        return $this->reportsMovie;
    }

    public function addReportsMovie(Report $reportsMovie): static
    {
        if (!$this->reportsMovie->contains($reportsMovie)) {
            $this->reportsMovie->add($reportsMovie);
            $reportsMovie->setTmdb($this);
        }

        return $this;
    }

    public function removeReportsMovie(Report $reportsMovie): static
    {
        if ($this->reportsMovie->removeElement($reportsMovie)) {
            // set the owning side to null (unless already changed)
            if ($reportsMovie->getTmdb() === $this) {
                $reportsMovie->setTmdb(null);
            }
        }

        return $this;
    }
}
