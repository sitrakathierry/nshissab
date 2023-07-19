<?php

namespace App\Entity;

use App\Repository\CmpTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CmpTypeRepository::class)]
class CmpType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\OneToMany(mappedBy: 'type', targetEntity: CmpOperation::class)]
    private Collection $cmpOperations;

    public function __construct()
    {
        $this->cmpOperations = new ArrayCollection();
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
     * @return Collection<int, CmpOperation>
     */
    public function getCmpOperations(): Collection
    {
        return $this->cmpOperations;
    }

    public function addCmpOperation(CmpOperation $cmpOperation): self
    {
        if (!$this->cmpOperations->contains($cmpOperation)) {
            $this->cmpOperations->add($cmpOperation);
            $cmpOperation->setType($this);
        }

        return $this;
    }

    public function removeCmpOperation(CmpOperation $cmpOperation): self
    {
        if ($this->cmpOperations->removeElement($cmpOperation)) {
            // set the owning side to null (unless already changed)
            if ($cmpOperation->getType() === $this) {
                $cmpOperation->setType(null);
            }
        }

        return $this;
    }
}
