<?php

namespace App\Entity;

use App\Repository\LvrStatutRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LvrStatutRepository::class)]
class LvrStatut
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $reference = null;

    #[ORM\OneToMany(mappedBy: 'lvrStatut', targetEntity: LvrDetails::class)]
    private Collection $lvrDetails;

    public function __construct()
    {
        $this->lvrDetails = new ArrayCollection();
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
     * @return Collection<int, LvrDetails>
     */
    public function getLvrDetails(): Collection
    {
        return $this->lvrDetails;
    }

    public function addLvrDetail(LvrDetails $lvrDetail): self
    {
        if (!$this->lvrDetails->contains($lvrDetail)) {
            $this->lvrDetails->add($lvrDetail);
            $lvrDetail->setLvrStatut($this);
        }

        return $this;
    }

    public function removeLvrDetail(LvrDetails $lvrDetail): self
    {
        if ($this->lvrDetails->removeElement($lvrDetail)) {
            // set the owning side to null (unless already changed)
            if ($lvrDetail->getLvrStatut() === $this) {
                $lvrDetail->setLvrStatut(null);
            }
        }

        return $this;
    }
}
