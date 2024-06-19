<?php

namespace App\Entity;

use App\Repository\AgcSexeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgcSexeRepository::class)]
class AgcSexe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $reference = null;

    #[ORM\OneToMany(mappedBy: 'sexe', targetEntity: CoiffEmployee::class)]
    private Collection $coiffEmployees;

    public function __construct()
    {
        $this->coiffEmployees = new ArrayCollection();
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
     * @return Collection<int, CoiffEmployee>
     */
    public function getCoiffEmployees(): Collection
    {
        return $this->coiffEmployees;
    }

    public function addCoiffEmployee(CoiffEmployee $coiffEmployee): self
    {
        if (!$this->coiffEmployees->contains($coiffEmployee)) {
            $this->coiffEmployees->add($coiffEmployee);
            $coiffEmployee->setSexe($this);
        }

        return $this;
    }

    public function removeCoiffEmployee(CoiffEmployee $coiffEmployee): self
    {
        if ($this->coiffEmployees->removeElement($coiffEmployee)) {
            // set the owning side to null (unless already changed)
            if ($coiffEmployee->getSexe() === $this) {
                $coiffEmployee->setSexe(null);
            }
        }

        return $this;
    }
}
