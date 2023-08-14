<?php

namespace App\Entity;

use App\Repository\IntLibelleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IntLibelleRepository::class)]
class IntLibelle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\OneToMany(mappedBy: 'libelle', targetEntity: IntMateriel::class)]
    private Collection $intMateriels;

    #[ORM\ManyToOne(inversedBy: 'intLibelles')]
    private ?Agence $agence = null;

    public function __construct()
    {
        $this->intMateriels = new ArrayCollection();
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
     * @return Collection<int, IntMateriel>
     */
    public function getIntMateriels(): Collection
    {
        return $this->intMateriels;
    }

    public function addIntMateriel(IntMateriel $intMateriel): self
    {
        if (!$this->intMateriels->contains($intMateriel)) {
            $this->intMateriels->add($intMateriel);
            $intMateriel->setLibelle($this);
        }

        return $this;
    }

    public function removeIntMateriel(IntMateriel $intMateriel): self
    {
        if ($this->intMateriels->removeElement($intMateriel)) {
            // set the owning side to null (unless already changed)
            if ($intMateriel->getLibelle() === $this) {
                $intMateriel->setLibelle(null);
            }
        }

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
}
