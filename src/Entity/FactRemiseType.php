<?php

namespace App\Entity;

use App\Repository\FactRemiseTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactRemiseTypeRepository::class)]
class FactRemiseType
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

    #[ORM\OneToMany(mappedBy: 'remiseType', targetEntity: Facture::class)]
    private Collection $factures;

    #[ORM\OneToMany(mappedBy: 'remiseType', targetEntity: FactDetails::class)]
    private Collection $factDetails;

    public function __construct()
    {
        $this->factures = new ArrayCollection();
        $this->factDetails = new ArrayCollection();
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

    /**
     * @return Collection<int, Facture>
     */
    public function getFactures(): Collection
    {
        return $this->factures;
    }

    public function addFacture(Facture $facture): self
    {
        if (!$this->factures->contains($facture)) {
            $this->factures->add($facture);
            $facture->setRemiseType($this);
        }

        return $this;
    }

    public function removeFacture(Facture $facture): self
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getRemiseType() === $this) {
                $facture->setRemiseType(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FactDetails>
     */
    public function getFactDetails(): Collection
    {
        return $this->factDetails;
    }

    public function addFactDetail(FactDetails $factDetail): self
    {
        if (!$this->factDetails->contains($factDetail)) {
            $this->factDetails->add($factDetail);
            $factDetail->setRemiseType($this);
        }

        return $this;
    }

    public function removeFactDetail(FactDetails $factDetail): self
    {
        if ($this->factDetails->removeElement($factDetail)) {
            // set the owning side to null (unless already changed)
            if ($factDetail->getRemiseType() === $this) {
                $factDetail->setRemiseType(null);
            }
        }

        return $this;
    }
}
