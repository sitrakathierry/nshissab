<?php

namespace App\Entity;

use App\Repository\LctStatutLoyerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LctStatutLoyerRepository::class)]
class LctStatutLoyer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\OneToMany(mappedBy: 'statut', targetEntity: LctRepartition::class)]
    private Collection $lctRepartitions;

    public function __construct()
    {
        $this->lctRepartitions = new ArrayCollection();
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
     * @return Collection<int, LctRepartition>
     */
    public function getLctRepartitions(): Collection
    {
        return $this->lctRepartitions;
    }

    public function addLctRepartition(LctRepartition $lctRepartition): self
    {
        if (!$this->lctRepartitions->contains($lctRepartition)) {
            $this->lctRepartitions->add($lctRepartition);
            $lctRepartition->setStatut($this);
        }

        return $this;
    }

    public function removeLctRepartition(LctRepartition $lctRepartition): self
    {
        if ($this->lctRepartitions->removeElement($lctRepartition)) {
            // set the owning side to null (unless already changed)
            if ($lctRepartition->getStatut() === $this) {
                $lctRepartition->setStatut(null);
            }
        }

        return $this;
    }
}
