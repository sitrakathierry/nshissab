<?php

namespace App\Entity;

use App\Repository\PrdCategoriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrdCategoriesRepository::class)]
class PrdCategories
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $images = null;

    #[ORM\ManyToOne(inversedBy: 'prdCategories')]
    private ?Agence $agence = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\OneToMany(mappedBy: 'categorie', targetEntity: PrdPreferences::class)]
    private Collection $prdPreferences;

    public function __construct()
    {
        $this->prdPreferences = new ArrayCollection();
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

    public function getImages(): ?string
    {
        return $this->images;
    }

    public function setImages(?string $images): self
    {
        $this->images = $images;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection<int, PrdPreferences>
     */
    public function getPrdPreferences(): Collection
    {
        return $this->prdPreferences;
    }

    public function addPrdPreference(PrdPreferences $prdPreference): self
    {
        if (!$this->prdPreferences->contains($prdPreference)) {
            $this->prdPreferences->add($prdPreference);
            $prdPreference->setCategorie($this);
        }

        return $this;
    }

    public function removePrdPreference(PrdPreferences $prdPreference): self
    {
        if ($this->prdPreferences->removeElement($prdPreference)) {
            // set the owning side to null (unless already changed)
            if ($prdPreference->getCategorie() === $this) {
                $prdPreference->setCategorie(null);
            }
        }

        return $this;
    }
}
