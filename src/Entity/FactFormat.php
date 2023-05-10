<?php

namespace App\Entity;

use App\Repository\FactFormatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactFormatRepository::class)]
class FactFormat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\OneToMany(mappedBy: 'format', targetEntity: FactDetails::class)]
    private Collection $factDetails;

    public function __construct()
    {
        $this->factDetails = new ArrayCollection();
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
     * @return Collection<int, FactDetails>
     */
    public function getFactDetails(): Collection
    {
        return $this->factDetails;
    }

    public function addFactDetail(FactDetails $factDetail): self
    {
        if (!$this->factDetails->contains($factDetail)) {
            $this->factDetails->add($factDetail);
            $factDetail->setFormat($this);
        }

        return $this;
    }

    public function removeFactDetail(FactDetails $factDetail): self
    {
        if ($this->factDetails->removeElement($factDetail)) {
            // set the owning side to null (unless already changed)
            if ($factDetail->getFormat() === $this) {
                $factDetail->setFormat(null);
            }
        }

        return $this;
    }
}
