<?php

namespace App\Entity;

use App\Repository\BtpEnonceeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BtpEnonceeRepository::class)]
class BtpEnoncee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\ManyToOne(inversedBy: 'btpEnoncees')]
    private ?Agence $agence = null;

    #[ORM\OneToMany(mappedBy: 'enonce', targetEntity: BtpCategorie::class)]
    private Collection $btpCategories;

    public function __construct()
    {
        $this->btpCategories = new ArrayCollection();
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

    public function isStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(?bool $statut): self
    {
        $this->statut = $statut;

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

    /**
     * @return Collection<int, BtpCategorie>
     */
    public function getBtpCategories(): Collection
    {
        return $this->btpCategories;
    }

    public function addBtpCategory(BtpCategorie $btpCategory): self
    {
        if (!$this->btpCategories->contains($btpCategory)) {
            $this->btpCategories->add($btpCategory);
            $btpCategory->setEnonce($this);
        }

        return $this;
    }

    public function removeBtpCategory(BtpCategorie $btpCategory): self
    {
        if ($this->btpCategories->removeElement($btpCategory)) {
            // set the owning side to null (unless already changed)
            if ($btpCategory->getEnonce() === $this) {
                $btpCategory->setEnonce(null);
            }
        }

        return $this;
    }
}
