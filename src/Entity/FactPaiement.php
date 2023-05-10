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

    public function __construct()
    {
        $this->factHistoPaiements = new ArrayCollection();
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
}
