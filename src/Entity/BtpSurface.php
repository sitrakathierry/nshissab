<?php

namespace App\Entity;

use App\Repository\BtpSurfaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BtpSurfaceRepository::class)]
class BtpSurface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\ManyToOne(inversedBy: 'btpSurfaces')]
    private ?Agence $agence = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'surface', targetEntity: FactSupDetailsPbat::class)]
    private Collection $factSupDetailsPbats;

    public function __construct()
    {
        $this->factSupDetailsPbats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function isStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(?bool $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getAgence(): ?Agence
    {
        return $this->agence;
    }

    public function setAgence(?Agence $agence): self
    {
        $this->agence = $agence;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, FactSupDetailsPbat>
     */
    public function getFactSupDetailsPbats(): Collection
    {
        return $this->factSupDetailsPbats;
    }

    public function addFactSupDetailsPbat(FactSupDetailsPbat $factSupDetailsPbat): self
    {
        if (!$this->factSupDetailsPbats->contains($factSupDetailsPbat)) {
            $this->factSupDetailsPbats->add($factSupDetailsPbat);
            $factSupDetailsPbat->setSurface($this);
        }

        return $this;
    }

    public function removeFactSupDetailsPbat(FactSupDetailsPbat $factSupDetailsPbat): self
    {
        if ($this->factSupDetailsPbats->removeElement($factSupDetailsPbat)) {
            // set the owning side to null (unless already changed)
            if ($factSupDetailsPbat->getSurface() === $this) {
                $factSupDetailsPbat->setSurface(null);
            }
        }

        return $this;
    }
}
