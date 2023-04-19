<?php

namespace App\Entity;

use App\Repository\PrdHistoEntrepotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrdHistoEntrepotRepository::class)]
class PrdHistoEntrepot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'prdHistoEntrepots')]
    private ?PrdEntrepot $entrepot = null;

    #[ORM\ManyToOne(inversedBy: 'prdHistoEntrepots')]
    private ?PrdVariationPrix $variationPrix = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $indice = null;

    #[ORM\Column(nullable: true)]
    private ?int $stock = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\OneToMany(mappedBy: 'histoEntrepot', targetEntity: PrdDeduction::class)]
    private Collection $prdDeductions;

    #[ORM\OneToMany(mappedBy: 'histoEntrepot', targetEntity: PrdApprovisionnement::class)]
    private Collection $prdApprovisionnements;

    public function __construct()
    {
        $this->prdDeductions = new ArrayCollection();
        $this->prdApprovisionnements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntrepot(): ?PrdEntrepot
    {
        return $this->entrepot;
    }

    public function setEntrepot(?PrdEntrepot $entrepot): self
    {
        $this->entrepot = $entrepot;

        return $this;
    }

    public function getVariationPrix(): ?PrdVariationPrix
    {
        return $this->variationPrix;
    }

    public function setVariationPrix(?PrdVariationPrix $variationPrix): self
    {
        $this->variationPrix = $variationPrix;

        return $this;
    }

    public function getIndice(): ?string
    {
        return $this->indice;
    }

    public function setIndice(?string $indice): self
    {
        $this->indice = $indice;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): self
    {
        $this->stock = $stock;

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
     * @return Collection<int, PrdDeduction>
     */
    public function getPrdDeductions(): Collection
    {
        return $this->prdDeductions;
    }

    public function addPrdDeduction(PrdDeduction $prdDeduction): self
    {
        if (!$this->prdDeductions->contains($prdDeduction)) {
            $this->prdDeductions->add($prdDeduction);
            $prdDeduction->setHistoEntrepot($this);
        }

        return $this;
    }

    public function removePrdDeduction(PrdDeduction $prdDeduction): self
    {
        if ($this->prdDeductions->removeElement($prdDeduction)) {
            // set the owning side to null (unless already changed)
            if ($prdDeduction->getHistoEntrepot() === $this) {
                $prdDeduction->setHistoEntrepot(null);
            }
        }

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
            $prdApprovisionnement->setHistoEntrepot($this);
        }

        return $this;
    }

    public function removePrdApprovisionnement(PrdApprovisionnement $prdApprovisionnement): self
    {
        if ($this->prdApprovisionnements->removeElement($prdApprovisionnement)) {
            // set the owning side to null (unless already changed)
            if ($prdApprovisionnement->getHistoEntrepot() === $this) {
                $prdApprovisionnement->setHistoEntrepot(null);
            }
        }

        return $this;
    }
}
