<?php

namespace App\Entity;

use App\Repository\SavDetailsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SavDetailsRepository::class)]
class SavDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'savDetails')]
    private ?SavAnnulation $annulation = null;

    #[ORM\ManyToOne(inversedBy: 'savDetails')]
    private ?FactDetails $factureDetail = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\ManyToOne(inversedBy: 'savDetails')]
    private ?Agence $agence = null;

    #[ORM\ManyToOne(inversedBy: 'savDetails')]
    private ?CaissePanier $caisseDetail = null;

    #[ORM\Column(nullable: true)]
    private ?bool $inStock = null;

    #[ORM\OneToMany(mappedBy: 'savDetail', targetEntity: PrdDeduction::class)]
    private Collection $prdDeductions;

    public function __construct()
    {
        $this->prdDeductions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnnulation(): ?SavAnnulation
    {
        return $this->annulation;
    }

    public function setAnnulation(?SavAnnulation $annulation): self
    {
        $this->annulation = $annulation;

        return $this;
    }

    public function getFactureDetail(): ?FactDetails
    {
        return $this->factureDetail;
    }

    public function setFactureDetail(?FactDetails $factureDetail): self
    {
        $this->factureDetail = $factureDetail;

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

    public function getCaisseDetail(): ?CaissePanier
    {
        return $this->caisseDetail;
    }

    public function setCaisseDetail(?CaissePanier $caisseDetail): self
    {
        $this->caisseDetail = $caisseDetail;

        return $this;
    }

    public function isInStock(): ?bool
    {
        return $this->inStock;
    }

    public function setInStock(?bool $inStock): self
    {
        $this->inStock = $inStock;

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
            $prdDeduction->setSavDetail($this);
        }

        return $this;
    }

    public function removePrdDeduction(PrdDeduction $prdDeduction): self
    {
        if ($this->prdDeductions->removeElement($prdDeduction)) {
            // set the owning side to null (unless already changed)
            if ($prdDeduction->getSavDetail() === $this) {
                $prdDeduction->setSavDetail(null);
            }
        }

        return $this;
    }
}
