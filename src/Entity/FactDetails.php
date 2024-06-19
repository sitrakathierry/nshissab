<?php

namespace App\Entity;

use App\Repository\FactDetailsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactDetailsRepository::class)]
class FactDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'factDetails')]
    private ?Facture $facture = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $activite = null;

    #[ORM\Column(nullable: true)]
    private ?int $entite = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $designation = null;

    #[ORM\Column(nullable: true)]
    private ?float $quantite = null;

    #[ORM\ManyToOne(inversedBy: 'factDetails')]
    private ?FactFormat $format = null;

    #[ORM\Column(nullable: true)]
    private ?float $prix = null;

    #[ORM\Column(nullable: true)]
    private ?float $remiseVal = null;

    #[ORM\ManyToOne(inversedBy: 'factDetails')]
    private ?FactRemiseType $remiseType = null;

    #[ORM\Column(nullable: true)]
    private ?float $tvaVal = null;

    #[ORM\OneToMany(mappedBy: 'factureDetail', targetEntity: LvrDetails::class)]
    private Collection $lvrDetails;

    #[ORM\OneToMany(mappedBy: 'factureDetail', targetEntity: SavDetails::class)]
    private Collection $savDetails;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\OneToMany(mappedBy: 'detail', targetEntity: FactSupDetailsPbat::class)]
    private Collection $factSupDetailsPbats;

    #[ORM\Column(nullable: true)]
    private ?bool $isForfait = null;

    #[ORM\ManyToOne(inversedBy: 'factDetails')]
    private ?PrdHistoEntrepot $histoEntrepot = null;

    #[ORM\ManyToOne(inversedBy: 'factDetails')]
    private ?CoiffCpPrix $coiffPrix = null;

    public function __construct()
    {
        $this->lvrDetails = new ArrayCollection();
        $this->savDetails = new ArrayCollection();
        $this->factSupDetailsPbats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getActivite(): ?string
    {
        return $this->activite;
    }

    public function setActivite(?string $activite): self
    {
        $this->activite = $activite;

        return $this;
    }

    public function getEntite(): ?int
    {
        return $this->entite;
    }

    public function setEntite(?int $entite): self
    {
        $this->entite = $entite;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(?float $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getFormat(): ?FactFormat
    {
        return $this->format;
    }

    public function setFormat(?FactFormat $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getRemiseVal(): ?float
    {
        return $this->remiseVal;
    }

    public function setRemiseVal(?float $remiseVal): self
    {
        $this->remiseVal = $remiseVal;

        return $this;
    }

    public function getRemiseType(): ?FactRemiseType
    {
        return $this->remiseType;
    }

    public function setRemiseType(?FactRemiseType $remiseType): self
    {
        $this->remiseType = $remiseType;

        return $this;
    }

    public function getTvaVal(): ?float
    {
        return $this->tvaVal;
    }

    public function setTvaVal(?float $tvaVal): self
    {
        $this->tvaVal = $tvaVal;

        return $this;
    }

    /**
     * @return Collection<int, LvrDetails>
     */
    public function getLvrDetails(): Collection
    {
        return $this->lvrDetails;
    }

    public function addLvrDetail(LvrDetails $lvrDetail): self
    {
        if (!$this->lvrDetails->contains($lvrDetail)) {
            $this->lvrDetails->add($lvrDetail);
            $lvrDetail->setFactureDetail($this);
        }

        return $this;
    }

    public function removeLvrDetail(LvrDetails $lvrDetail): self
    {
        if ($this->lvrDetails->removeElement($lvrDetail)) {
            // set the owning side to null (unless already changed)
            if ($lvrDetail->getFactureDetail() === $this) {
                $lvrDetail->setFactureDetail(null);
            }
        }

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
            $savDetail->setFactureDetail($this);
        }

        return $this;
    }

    public function removeSavDetail(SavDetails $savDetail): self
    {
        if ($this->savDetails->removeElement($savDetail)) {
            // set the owning side to null (unless already changed)
            if ($savDetail->getFactureDetail() === $this) {
                $savDetail->setFactureDetail(null);
            }
        }

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
            $factSupDetailsPbat->setDetail($this);
        }

        return $this;
    }

    public function removeFactSupDetailsPbat(FactSupDetailsPbat $factSupDetailsPbat): self
    {
        if ($this->factSupDetailsPbats->removeElement($factSupDetailsPbat)) {
            // set the owning side to null (unless already changed)
            if ($factSupDetailsPbat->getDetail() === $this) {
                $factSupDetailsPbat->setDetail(null);
            }
        }

        return $this;
    }

    public function isIsForfait(): ?bool
    {
        return $this->isForfait;
    }

    public function setIsForfait(?bool $isForfait): self
    {
        $this->isForfait = $isForfait;

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

    public function getCoiffPrix(): ?CoiffCpPrix
    {
        return $this->coiffPrix;
    }

    public function setCoiffPrix(?CoiffCpPrix $coiffPrix): self
    {
        $this->coiffPrix = $coiffPrix;

        return $this;
    }
}
