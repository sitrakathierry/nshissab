<?php

namespace App\Entity;

use App\Repository\CoiffCoupesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoiffCoupesRepository::class)]
class CoiffCoupes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'coiffCoupes')]
    private ?Agence $agence = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nom = null;

    #[ORM\ManyToOne(inversedBy: 'coiffCoupes')]
    private ?CoiffCategorie $categorie = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\OneToMany(mappedBy: 'coupes', targetEntity: CoiffCpPrix::class)]
    private Collection $coiffCpPrixes;

    public function __construct()
    {
        $this->coiffCpPrixes = new ArrayCollection();
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCategorie(): ?CoiffCategorie
    {
        return $this->categorie;
    }

    public function setCategorie(?CoiffCategorie $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

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
     * @return Collection<int, CoiffCpPrix>
     */
    public function getCoiffCpPrixes(): Collection
    {
        return $this->coiffCpPrixes;
    }

    public function addCoiffCpPrix(CoiffCpPrix $coiffCpPrix): self
    {
        if (!$this->coiffCpPrixes->contains($coiffCpPrix)) {
            $this->coiffCpPrixes->add($coiffCpPrix);
            $coiffCpPrix->setCoupes($this);
        }

        return $this;
    }

    public function removeCoiffCpPrix(CoiffCpPrix $coiffCpPrix): self
    {
        if ($this->coiffCpPrixes->removeElement($coiffCpPrix)) {
            // set the owning side to null (unless already changed)
            if ($coiffCpPrix->getCoupes() === $this) {
                $coiffCpPrix->setCoupes(null);
            }
        }

        return $this;
    }
}
