<?php

namespace App\Entity;

use App\Repository\PrdMargeTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrdMargeTypeRepository::class)]
class PrdMargeType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $notation = null;

    #[ORM\Column(nullable: true)]
    private ?int $calcul = null;

    #[ORM\ManyToOne(inversedBy: 'prdMargeTypes')]
    private ?Agence $agence = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\OneToMany(mappedBy: 'margeType', targetEntity: PrdApprovisionnement::class)]
    private Collection $prdApprovisionnements;

    public function __construct()
    {
        $this->prdApprovisionnements = new ArrayCollection();
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

    public function getNotation(): ?string
    {
        return $this->notation;
    }

    public function setNotation(?string $notation): self
    {
        $this->notation = $notation;

        return $this;
    }

    public function getCalcul(): ?int
    {
        return $this->calcul;
    }

    public function setCalcul(?int $calcul): self
    {
        $this->calcul = $calcul;

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
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection<int, PrdApprovisionnement>
     */
    public function getPrdApprovisionnements(): Collection
    {
        return $this->prdApprovisionnements;
    }

    public function addPrdApprovisionnement(PrdApprovisionnement $prdApprovisionnement): self
    {
        if (!$this->prdApprovisionnements->contains($prdApprovisionnement)) {
            $this->prdApprovisionnements->add($prdApprovisionnement);
            $prdApprovisionnement->setMargeType($this);
        }

        return $this;
    }

    public function removePrdApprovisionnement(PrdApprovisionnement $prdApprovisionnement): self
    {
        if ($this->prdApprovisionnements->removeElement($prdApprovisionnement)) {
            // set the owning side to null (unless already changed)
            if ($prdApprovisionnement->getMargeType() === $this) {
                $prdApprovisionnement->setMargeType(null);
            }
        }

        return $this;
    }
}
