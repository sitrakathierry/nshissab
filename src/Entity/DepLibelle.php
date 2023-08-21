<?php

namespace App\Entity;

use App\Repository\DepLibelleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepLibelleRepository::class)]
class DepLibelle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'depLibelles')]
    private ?Agence $agence = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nom = null;

    #[ORM\OneToMany(mappedBy: 'libelle', targetEntity: DepDetails::class)]
    private Collection $depDetails;

    public function __construct()
    {
        $this->depDetails = new ArrayCollection();
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

    /**
     * @return Collection<int, DepDetails>
     */
    public function getDepDetails(): Collection
    {
        return $this->depDetails;
    }

    public function addDepDetail(DepDetails $depDetail): self
    {
        if (!$this->depDetails->contains($depDetail)) {
            $this->depDetails->add($depDetail);
            $depDetail->setLibelle($this);
        }

        return $this;
    }

    public function removeDepDetail(DepDetails $depDetail): self
    {
        if ($this->depDetails->removeElement($depDetail)) {
            // set the owning side to null (unless already changed)
            if ($depDetail->getLibelle() === $this) {
                $depDetail->setLibelle(null);
            }
        }

        return $this;
    }
}
