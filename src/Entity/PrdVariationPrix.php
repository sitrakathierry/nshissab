<?php

namespace App\Entity;

use App\Repository\PrdVariationPrixRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrdVariationPrixRepository::class)]
class PrdVariationPrix
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'prdVariationPrixes')]
    private ?Produit $produit = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixVente = null;

    #[ORM\Column(nullable: true)]
    private ?float $stock = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\OneToMany(mappedBy: 'variationPrix', targetEntity: PrdHistoEntrepot::class)]
    private Collection $prdHistoEntrepots;

    #[ORM\OneToMany(mappedBy: 'variationPrix', targetEntity: PrdApprovisionnement::class)]
    private Collection $prdApprovisionnements;

    #[ORM\Column(nullable: true)]
    private ?float $stock_alert = null;

    #[ORM\OneToMany(mappedBy: 'variationPrix', targetEntity: PrdSolde::class)]
    private Collection $prdSoldes;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $indice = null;

    #[ORM\OneToMany(mappedBy: 'variationPrix', targetEntity: CaissePanier::class)]
    private Collection $caissePaniers;

    public function __construct()
    {
        $this->prdHistoEntrepots = new ArrayCollection();
        $this->prdApprovisionnements = new ArrayCollection();
        $this->prdSoldes = new ArrayCollection();
        $this->caissePaniers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;

        return $this;
    }

    public function getPrixVente(): ?float
    {
        return $this->prixVente;
    }

    public function setPrixVente(?float $prixVente): self
    {
        $this->prixVente = $prixVente;

        return $this;
    }

    public function getStock(): ?float
    {
        return $this->stock;
    }

    public function setStock(?float $stock): self
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
     * @return Collection<int, PrdHistoEntrepot>
     */
    public function getPrdHistoEntrepots(): Collection
    {
        return $this->prdHistoEntrepots;
    }

    public function addPrdHistoEntrepot(PrdHistoEntrepot $prdHistoEntrepot): self
    {
        if (!$this->prdHistoEntrepots->contains($prdHistoEntrepot)) {
            $this->prdHistoEntrepots->add($prdHistoEntrepot);
            $prdHistoEntrepot->setVariationPrix($this);
        }

        return $this;
    }

    public function removePrdHistoEntrepot(PrdHistoEntrepot $prdHistoEntrepot): self
    {
        if ($this->prdHistoEntrepots->removeElement($prdHistoEntrepot)) {
            // set the owning side to null (unless already changed)
            if ($prdHistoEntrepot->getVariationPrix() === $this) {
                $prdHistoEntrepot->setVariationPrix(null);
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
            $prdApprovisionnement->setVariationPrix($this);
        }

        return $this;
    }

    public function removePrdApprovisionnement(PrdApprovisionnement $prdApprovisionnement): self
    {
        if ($this->prdApprovisionnements->removeElement($prdApprovisionnement)) {
            // set the owning side to null (unless already changed)
            if ($prdApprovisionnement->getVariationPrix() === $this) {
                $prdApprovisionnement->setVariationPrix(null);
            }
        }

        return $this;
    }

    public function getStockAlert(): ?float
    {
        return $this->stock_alert;
    }

    public function setStockAlert(?float $stock_alert): self
    {
        $this->stock_alert = $stock_alert;

        return $this;
    }

    /**
     * @return Collection<int, PrdSolde>
     */
    public function getPrdSoldes(): Collection
    {
        return $this->prdSoldes;
    }

    public function addPrdSolde(PrdSolde $prdSolde): self
    {
        if (!$this->prdSoldes->contains($prdSolde)) {
            $this->prdSoldes->add($prdSolde);
            $prdSolde->setVariationPrix($this);
        }

        return $this;
    }

    public function removePrdSolde(PrdSolde $prdSolde): self
    {
        if ($this->prdSoldes->removeElement($prdSolde)) {
            // set the owning side to null (unless already changed)
            if ($prdSolde->getVariationPrix() === $this) {
                $prdSolde->setVariationPrix(null);
            }
        }

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

    /**
     * @return Collection<int, CaissePanier>
     */
    public function getCaissePaniers(): Collection
    {
        return $this->caissePaniers;
    }

    public function addCaissePanier(CaissePanier $caissePanier): self
    {
        if (!$this->caissePaniers->contains($caissePanier)) {
            $this->caissePaniers->add($caissePanier);
            $caissePanier->setVariationPrix($this);
        }

        return $this;
    }

    public function removeCaissePanier(CaissePanier $caissePanier): self
    {
        if ($this->caissePaniers->removeElement($caissePanier)) {
            // set the owning side to null (unless already changed)
            if ($caissePanier->getVariationPrix() === $this) {
                $caissePanier->setVariationPrix(null);
            }
        }

        return $this;
    }
}
