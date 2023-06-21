<?php

namespace App\Entity;

use App\Repository\FactModeleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactModeleRepository::class)]
class FactModele
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\OneToMany(mappedBy: 'modele', targetEntity: Facture::class)]
    private Collection $factures;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(nullable: true)]
    private ?int $rang = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'factModeles')]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $factModeles;

    public function __construct()
    {
        $this->factures = new ArrayCollection();
        $this->factModeles = new ArrayCollection();
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
            $facture->setModele($this);
        }

        return $this;
    }

    public function removeFacture(Facture $facture): self
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getModele() === $this) {
                $facture->setModele(null);
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

    public function getRang(): ?int
    {
        return $this->rang;
    }

    public function setRang(?int $rang): self
    {
        $this->rang = $rang;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getFactModeles(): Collection
    {
        return $this->factModeles;
    }

    public function addFactModele(self $factModele): self
    {
        if (!$this->factModeles->contains($factModele)) {
            $this->factModeles->add($factModele);
            $factModele->setParent($this);
        }

        return $this;
    }

    public function removeFactModele(self $factModele): self
    {
        if ($this->factModeles->removeElement($factModele)) {
            // set the owning side to null (unless already changed)
            if ($factModele->getParent() === $this) {
                $factModele->setParent(null);
            }
        }

        return $this;
    }
}
