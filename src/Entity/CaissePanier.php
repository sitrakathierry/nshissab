<?php

namespace App\Entity;

use App\Repository\CaissePanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CaissePanierRepository::class)]
class CaissePanier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'caissePaniers')]
    private ?CaisseCommande $commande = null;

    #[ORM\ManyToOne(inversedBy: 'caissePaniers')]
    private ?PrdHistoEntrepot $histoEntrepot = null;

    #[ORM\Column(nullable: true)]
    private ?int $prix = null;

    #[ORM\Column(nullable: true)]
    private ?int $quantite = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\Column(nullable: true)]
    private ?float $tva = null;

    #[ORM\ManyToOne(inversedBy: 'caissePaniers')]
    private ?PrdVariationPrix $variationPrix = null;

    #[ORM\ManyToOne(inversedBy: 'caissePaniers')]
    private ?Agence $agence = null;

    #[ORM\OneToMany(mappedBy: 'caisseDetail', targetEntity: SavDetails::class)]
    private Collection $savDetails;

    public function __construct()
    {
        $this->savDetails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommande(): ?CaisseCommande
    {
        return $this->commande;
    }

    public function setCommande(?CaisseCommande $commande): self
    {
        $this->commande = $commande;

        return $this;
    }

    public function getHistoEntrepot(): ?PrdHistoEntrepot
    {
        return $this->histoEntrepot;
    }

    public function setHistoEntrepot(?PrdHistoEntrepot $histoEntrepot): self
    {
        $this->histoEntrepot = $histoEntrepot;

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(?int $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(?int $quantite): self
    {
        $this->quantite = $quantite;

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

    public function getTva(): ?float
    {
        return $this->tva;
    }

    public function setTva(?float $tva): self
    {
        $this->tva = $tva;

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

    public function getAgence(): ?Agence
    {
        return $this->agence;
    }

    public function setAgence(?Agence $agence): self
    {
        $this->agence = $agence;

        return $this;
    }

    /**
     * @return Collection<int, SavDetails>
     */
    public function getSavDetails(): Collection
    {
        return $this->savDetails;
    }

    public function addSavDetail(SavDetails $savDetail): self
    {
        if (!$this->savDetails->contains($savDetail)) {
            $this->savDetails->add($savDetail);
            $savDetail->setCaisseDetail($this);
        }

        return $this;
    }

    public function removeSavDetail(SavDetails $savDetail): self
    {
        if ($this->savDetails->removeElement($savDetail)) {
            // set the owning side to null (unless already changed)
            if ($savDetail->getCaisseDetail() === $this) {
                $savDetail->setCaisseDetail(null);
            }
        }

        return $this;
    }
}
