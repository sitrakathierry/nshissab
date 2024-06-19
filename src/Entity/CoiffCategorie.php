<?php

namespace App\Entity;

use App\Repository\CoiffCategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoiffCategorieRepository::class)]
class CoiffCategorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nom = null;

    #[ORM\ManyToOne(inversedBy: 'coiffCategories')]
    private ?Agence $agence = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\OneToMany(mappedBy: 'categorie', targetEntity: CoiffCoupes::class)]
    private Collection $coiffCoupes;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $genre = null;

    public function __construct()
    {
        $this->coiffCoupes = new ArrayCollection();
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

    public function getAgence(): ?Agence
    {
        return $this->agence;
    }

    public function setAgence(?Agence $agence): self
    {
        $this->agence = $agence;

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
     * @return Collection<int, CoiffCoupes>
     */
    public function getCoiffCoupes(): Collection
    {
        return $this->coiffCoupes;
    }

    public function addCoiffCoupe(CoiffCoupes $coiffCoupe): self
    {
        if (!$this->coiffCoupes->contains($coiffCoupe)) {
            $this->coiffCoupes->add($coiffCoupe);
            $coiffCoupe->setCategorie($this);
        }

        return $this;
    }

    public function removeCoiffCoupe(CoiffCoupes $coiffCoupe): self
    {
        if ($this->coiffCoupes->removeElement($coiffCoupe)) {
            // set the owning side to null (unless already changed)
            if ($coiffCoupe->getCategorie() === $this) {
                $coiffCoupe->setCategorie(null);
            }
        }

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(?string $genre): self
    {
        $this->genre = $genre;

        return $this;
    }
}
