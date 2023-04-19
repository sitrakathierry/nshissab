<?php

namespace App\Entity;

use App\Repository\AgcDeviseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgcDeviseRepository::class)]
class AgcDevise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $symbole = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lettre = null;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: Agence::class)]
    private Collection $agences;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: PrdEntrepot::class)]
    private Collection $prdEntrepots;

    public function __construct()
    {
        $this->agences = new ArrayCollection();
        $this->prdEntrepots = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSymbole(): ?string
    {
        return $this->symbole;
    }

    public function setSymbole(?string $symbole): self
    {
        $this->symbole = $symbole;

        return $this;
    }

    public function getLettre(): ?string
    {
        return $this->lettre;
    }

    public function setLettre(?string $lettre): self
    {
        $this->lettre = $lettre;

        return $this;
    }

    /**
     * @return Collection<int, Agence>
     */
    public function getAgences(): Collection
    {
        return $this->agences;
    }

    public function addAgence(Agence $agence): self
    {
        if (!$this->agences->contains($agence)) {
            $this->agences->add($agence);
            $agence->setDevise($this);
        }

        return $this;
    }

    public function removeAgence(Agence $agence): self
    {
        if ($this->agences->removeElement($agence)) {
            // set the owning side to null (unless already changed)
            if ($agence->getDevise() === $this) {
                $agence->setDevise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PrdEntrepot>
     */
    public function getPrdEntrepots(): Collection
    {
        return $this->prdEntrepots;
    }

    public function addPrdEntrepot(PrdEntrepot $prdEntrepot): self
    {
        if (!$this->prdEntrepots->contains($prdEntrepot)) {
            $this->prdEntrepots->add($prdEntrepot);
            $prdEntrepot->setDevise($this);
        }

        return $this;
    }

    public function removePrdEntrepot(PrdEntrepot $prdEntrepot): self
    {
        if ($this->prdEntrepots->removeElement($prdEntrepot)) {
            // set the owning side to null (unless already changed)
            if ($prdEntrepot->getDevise() === $this) {
                $prdEntrepot->setDevise(null);
            }
        }

        return $this;
    }
}
