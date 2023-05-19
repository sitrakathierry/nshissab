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
    private ?int $quantite = null;

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

    public function __construct()
    {
        $this->lvrDetails = new ArrayCollection();
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

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(?int $quantite): self
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
}
