<?php

namespace App\Entity;

use App\Repository\LctTypeLocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LctTypeLocationRepository::class)]
class LctTypeLocation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\OneToMany(mappedBy: 'typeLocation', targetEntity: LctContrat::class)]
    private Collection $lctContrats;

    public function __construct()
    {
        $this->lctContrats = new ArrayCollection();
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

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @return Collection<int, LctContrat>
     */
    public function getLctContrats(): Collection
    {
        return $this->lctContrats;
    }

    public function addLctContrat(LctContrat $lctContrat): self
    {
        if (!$this->lctContrats->contains($lctContrat)) {
            $this->lctContrats->add($lctContrat);
            $lctContrat->setTypeLocation($this);
        }

        return $this;
    }

    public function removeLctContrat(LctContrat $lctContrat): self
    {
        if ($this->lctContrats->removeElement($lctContrat)) {
            // set the owning side to null (unless already changed)
            if ($lctContrat->getTypeLocation() === $this) {
                $lctContrat->setTypeLocation(null);
            }
        }

        return $this;
    }
}
