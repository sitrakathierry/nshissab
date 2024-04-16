<?php

namespace App\Entity;

use App\Repository\PrdEntrepotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrdEntrepotRepository::class)]
class PrdEntrepot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'prdEntrepots')]
    private ?Agence $agence = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\ManyToOne(inversedBy: 'prdEntrepots')]
    private ?AgcDevise $devise = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\OneToMany(mappedBy: 'entrepot', targetEntity: PrdHistoEntrepot::class)]
    private Collection $prdHistoEntrepots;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\OneToMany(mappedBy: 'entrepot', targetEntity: PrdEntrpAffectation::class)]
    private Collection $prdEntrpAffectations;

    public function __construct()
    {
        $this->prdHistoEntrepots = new ArrayCollection();
        $this->prdEntrpAffectations = new ArrayCollection();
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getDevise(): ?AgcDevise
    {
        return $this->devise;
    }

    public function setDevise(?AgcDevise $devise): self
    {
        $this->devise = $devise;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection<int, PrdHistoEntrepot>
     */
    public function getPrdHistoEntrepots(): Collection
    {
        return $this->prdHistoEntrepots;
    }

    public function addPrdHistoEntrepot(PrdHistoEntrepot $prdHistoEntrepot): self
    {
        if (!$this->prdHistoEntrepots->contains($prdHistoEntrepot)) {
            $this->prdHistoEntrepots->add($prdHistoEntrepot);
            $prdHistoEntrepot->setEntrepot($this);
        }

        return $this;
    }

    public function removePrdHistoEntrepot(PrdHistoEntrepot $prdHistoEntrepot): self
    {
        if ($this->prdHistoEntrepots->removeElement($prdHistoEntrepot)) {
            // set the owning side to null (unless already changed)
            if ($prdHistoEntrepot->getEntrepot() === $this) {
                $prdHistoEntrepot->setEntrepot(null);
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
     * @return Collection<int, PrdEntrpAffectation>
     */
    public function getPrdEntrpAffectations(): Collection
    {
        return $this->prdEntrpAffectations;
    }

    public function addPrdEntrpAffectation(PrdEntrpAffectation $prdEntrpAffectation): self
    {
        if (!$this->prdEntrpAffectations->contains($prdEntrpAffectation)) {
            $this->prdEntrpAffectations->add($prdEntrpAffectation);
            $prdEntrpAffectation->setEntrepot($this);
        }

        return $this;
    }

    public function removePrdEntrpAffectation(PrdEntrpAffectation $prdEntrpAffectation): self
    {
        if ($this->prdEntrpAffectations->removeElement($prdEntrpAffectation)) {
            // set the owning side to null (unless already changed)
            if ($prdEntrpAffectation->getEntrepot() === $this) {
                $prdEntrpAffectation->setEntrepot(null);
            }
        }

        return $this;
    }
}
