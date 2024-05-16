<?php

namespace App\Entity;

use App\Repository\FactPaiementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactPaiementRepository::class)]
class FactPaiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\OneToMany(mappedBy: 'paiement', targetEntity: FactHistoPaiement::class)]
    private Collection $factHistoPaiements;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numCaption = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $libelleCaption = null;

    #[ORM\Column(nullable: true)]
    private ?int $rang = null;

    #[ORM\OneToMany(mappedBy: 'paiement', targetEntity: CrdFinance::class)]
    private Collection $crdFinances;

    #[ORM\OneToMany(mappedBy: 'paiement', targetEntity: CrdDetails::class)]
    private Collection $crdDetails;

    #[ORM\OneToMany(mappedBy: 'paiement', targetEntity: AgdEcheance::class)]
    private Collection $agdEcheances;

    public function __construct()
    {
        $this->factHistoPaiements = new ArrayCollection();
        $this->crdFinances = new ArrayCollection();
        $this->crdDetails = new ArrayCollection();
        $this->agdEcheances = new ArrayCollection();
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

    /**
     * @return Collection<int, FactHistoPaiement>
     */
    public function getFactHistoPaiements(): Collection
    {
        return $this->factHistoPaiements;
    }

    public function addFactHistoPaiement(FactHistoPaiement $factHistoPaiement): self
    {
        if (!$this->factHistoPaiements->contains($factHistoPaiement)) {
            $this->factHistoPaiements->add($factHistoPaiement);
            $factHistoPaiement->setPaiement($this);
        }

        return $this;
    }

    public function removeFactHistoPaiement(FactHistoPaiement $factHistoPaiement): self
    {
        if ($this->factHistoPaiements->removeElement($factHistoPaiement)) {
            // set the owning side to null (unless already changed)
            if ($factHistoPaiement->getPaiement() === $this) {
                $factHistoPaiement->setPaiement(null);
            }
        }

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getNumCaption(): ?string
    {
        return $this->numCaption;
    }

    public function setNumCaption(?string $numCaption): self
    {
        $this->numCaption = $numCaption;

        return $this;
    }

    public function getLibelleCaption(): ?string
    {
        return $this->libelleCaption;
    }

    public function setLibelleCaption(?string $libelleCaption): self
    {
        $this->libelleCaption = $libelleCaption;

        return $this;
    }

    public function getRang(): ?int
    {
        return $this->rang;
    }

    public function setRang(?int $rang): self
    {
        $this->rang = $rang;

        return $this;
    }

    /**
     * @return Collection<int, CrdFinance>
     */
    public function getCrdFinances(): Collection
    {
        return $this->crdFinances;
    }

    public function addCrdFinance(CrdFinance $crdFinance): self
    {
        if (!$this->crdFinances->contains($crdFinance)) {
            $this->crdFinances->add($crdFinance);
            $crdFinance->setPaiement($this);
        }

        return $this;
    }

    public function removeCrdFinance(CrdFinance $crdFinance): self
    {
        if ($this->crdFinances->removeElement($crdFinance)) {
            // set the owning side to null (unless already changed)
            if ($crdFinance->getPaiement() === $this) {
                $crdFinance->setPaiement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CrdDetails>
     */
    public function getCrdDetails(): Collection
    {
        return $this->crdDetails;
    }

    public function addCrdDetail(CrdDetails $crdDetail): self
    {
        if (!$this->crdDetails->contains($crdDetail)) {
            $this->crdDetails->add($crdDetail);
            $crdDetail->setPaiement($this);
        }

        return $this;
    }

    public function removeCrdDetail(CrdDetails $crdDetail): self
    {
        if ($this->crdDetails->removeElement($crdDetail)) {
            // set the owning side to null (unless already changed)
            if ($crdDetail->getPaiement() === $this) {
                $crdDetail->setPaiement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AgdEcheance>
     */
    public function getAgdEcheances(): Collection
    {
        return $this->agdEcheances;
    }

    public function addAgdEcheance(AgdEcheance $agdEcheance): self
    {
        if (!$this->agdEcheances->contains($agdEcheance)) {
            $this->agdEcheances->add($agdEcheance);
            $agdEcheance->setPaiement($this);
        }

        return $this;
    }

    public function removeAgdEcheance(AgdEcheance $agdEcheance): self
    {
        if ($this->agdEcheances->removeElement($agdEcheance)) {
            // set the owning side to null (unless already changed)
            if ($agdEcheance->getPaiement() === $this) {
                $agdEcheance->setPaiement(null);
            }
        }

        return $this;
    }
}
