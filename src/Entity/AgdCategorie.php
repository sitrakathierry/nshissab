<?php

namespace App\Entity;

use App\Repository\AgdCategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgdCategorieRepository::class)]
class AgdCategorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\OneToMany(mappedBy: 'categorie', targetEntity: AgdEcheance::class)]
    private Collection $agdEcheances;

    public function __construct()
    {
        $this->agdEcheances = new ArrayCollection();
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
     * @return Collection<int, AgdEcheance>
     */
    public function getAgdEcheances(): Collection
    {
        return $this->agdEcheances;
    }

    public function addAgdEcheance(AgdEcheance $agdEcheance): self
    {
        if (!$this->agdEcheances->contains($agdEcheance)) {
            $this->agdEcheances->add($agdEcheance);
            $agdEcheance->setCategorie($this);
        }

        return $this;
    }

    public function removeAgdEcheance(AgdEcheance $agdEcheance): self
    {
        if ($this->agdEcheances->removeElement($agdEcheance)) {
            // set the owning side to null (unless already changed)
            if ($agdEcheance->getCategorie() === $this) {
                $agdEcheance->setCategorie(null);
            }
        }

        return $this;
    }
}
