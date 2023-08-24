<?php

namespace App\Entity;

use App\Repository\ChkTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChkTypeRepository::class)]
class ChkType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\OneToMany(mappedBy: 'type', targetEntity: ChkCheque::class)]
    private Collection $chkCheques;

    public function __construct()
    {
        $this->chkCheques = new ArrayCollection();
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
     * @return Collection<int, ChkCheque>
     */
    public function getChkCheques(): Collection
    {
        return $this->chkCheques;
    }

    public function addChkCheque(ChkCheque $chkCheque): self
    {
        if (!$this->chkCheques->contains($chkCheque)) {
            $this->chkCheques->add($chkCheque);
            $chkCheque->setType($this);
        }

        return $this;
    }

    public function removeChkCheque(ChkCheque $chkCheque): self
    {
        if ($this->chkCheques->removeElement($chkCheque)) {
            // set the owning side to null (unless already changed)
            if ($chkCheque->getType() === $this) {
                $chkCheque->setType(null);
            }
        }

        return $this;
    }
}
