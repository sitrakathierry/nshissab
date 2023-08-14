<?php

namespace App\Entity;

use App\Repository\IntTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IntTypeRepository::class)]
class IntType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\OneToMany(mappedBy: 'type', targetEntity: IntMouvement::class)]
    private Collection $intMouvements;

    public function __construct()
    {
        $this->intMouvements = new ArrayCollection();
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
     * @return Collection<int, IntMouvement>
     */
    public function getIntMouvements(): Collection
    {
        return $this->intMouvements;
    }

    public function addIntMouvement(IntMouvement $intMouvement): self
    {
        if (!$this->intMouvements->contains($intMouvement)) {
            $this->intMouvements->add($intMouvement);
            $intMouvement->setType($this);
        }

        return $this;
    }

    public function removeIntMouvement(IntMouvement $intMouvement): self
    {
        if ($this->intMouvements->removeElement($intMouvement)) {
            // set the owning side to null (unless already changed)
            if ($intMouvement->getType() === $this) {
                $intMouvement->setType(null);
            }
        }

        return $this;
    }
}
