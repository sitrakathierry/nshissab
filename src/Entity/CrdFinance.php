<?php

namespace App\Entity;

use App\Repository\CrdFinanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CrdFinanceRepository::class)]
class CrdFinance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'crdFinances')]
    private ?Agence $agence = null;

    #[ORM\ManyToOne(inversedBy: 'crdFinances')]
    private ?Facture $facture = null;

    #[ORM\ManyToOne(inversedBy: 'crdFinances')]
    private ?FactPaiement $paiement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numFnc = null;

    #[ORM\ManyToOne(inversedBy: 'crdFinances')]
    private ?CrdStatut $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'finance', targetEntity: CrdDetails::class)]
    private Collection $crdDetails;

    #[ORM\OneToMany(mappedBy: 'catTable', targetEntity: AgdEcheance::class)]
    private Collection $agdEcheances;

    #[ORM\OneToMany(mappedBy: 'acompte', targetEntity: AgdAcompte::class)]
    private Collection $agdAcomptes;

    #[ORM\Column(nullable: true)]
    private ?int $anneeData = null;

    public function __construct()
    {
        $this->crdDetails = new ArrayCollection();
        $this->agdEcheances = new ArrayCollection();
        $this->agdAcomptes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(?Facture $facture): self
    {
        $this->facture = $facture;

        return $this;
    }

    public function getPaiement(): ?FactPaiement
    {
        return $this->paiement;
    }

    public function setPaiement(?FactPaiement $paiement): self
    {
        $this->paiement = $paiement;

        return $this;
    }

    public function getNumFnc(): ?string
    {
        return $this->numFnc;
    }

    public function setNumFnc(?string $numFnc): self
    {
        $this->numFnc = $numFnc;

        return $this;
    }

    public function getStatut(): ?CrdStatut
    {
        return $this->statut;
    }

    public function setStatut(?CrdStatut $statut): self
    {
        $this->statut = $statut;

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
            $crdDetail->setFinance($this);
        }

        return $this;
    }

    public function removeCrdDetail(CrdDetails $crdDetail): self
    {
        if ($this->crdDetails->removeElement($crdDetail)) {
            // set the owning side to null (unless already changed)
            if ($crdDetail->getFinance() === $this) {
                $crdDetail->setFinance(null);
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
            $agdEcheance->setCatTable($this);
        }

        return $this;
    }

    public function removeAgdEcheance(AgdEcheance $agdEcheance): self
    {
        if ($this->agdEcheances->removeElement($agdEcheance)) {
            // set the owning side to null (unless already changed)
            if ($agdEcheance->getCatTable() === $this) {
                $agdEcheance->setCatTable(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AgdAcompte>
     */
    public function getAgdAcomptes(): Collection
    {
        return $this->agdAcomptes;
    }

    public function addAgdAcompte(AgdAcompte $agdAcompte): self
    {
        if (!$this->agdAcomptes->contains($agdAcompte)) {
            $this->agdAcomptes->add($agdAcompte);
            $agdAcompte->setAcompte($this);
        }

        return $this;
    }

    public function removeAgdAcompte(AgdAcompte $agdAcompte): self
    {
        if ($this->agdAcomptes->removeElement($agdAcompte)) {
            // set the owning side to null (unless already changed)
            if ($agdAcompte->getAcompte() === $this) {
                $agdAcompte->setAcompte(null);
            }
        }

        return $this;
    }

    public function getAnneeData(): ?int
    {
        return $this->anneeData;
    }

    public function setAnneeData(?int $anneeData): self
    {
        $this->anneeData = $anneeData;

        return $this;
    }
}
