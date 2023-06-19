<?php

namespace App\Entity;

use App\Repository\BtpMesureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BtpMesureRepository::class)]
class BtpMesure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $notation = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\ManyToOne(inversedBy: 'btpMesures')]
    private ?Agence $agence = null;

    #[ORM\OneToMany(mappedBy: 'mesure', targetEntity: BtpElement::class)]
    private Collection $btpElements;

    #[ORM\OneToMany(mappedBy: 'mesure', targetEntity: BtpCategorie::class)]
    private Collection $btpCategories;

    public function __construct()
    {
        $this->btpElements = new ArrayCollection();
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

    public function getNotation(): ?string
    {
        return $this->notation;
    }

    public function setNotation(?string $notation): self
    {
        $this->notation = $notation;

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
     * @return Collection<int, BtpElement>
     */
    public function getBtpElements(): Collection
    {
        return $this->btpElements;
    }

    public function addBtpElement(BtpElement $btpElement): self
    {
        if (!$this->btpElements->contains($btpElement)) {
            $this->btpElements->add($btpElement);
            $btpElement->setMesure($this);
        }

        return $this;
    }

    public function removeBtpElement(BtpElement $btpElement): self
    {
        if ($this->btpElements->removeElement($btpElement)) {
            // set the owning side to null (unless already changed)
            if ($btpElement->getMesure() === $this) {
                $btpElement->setMesure(null);
            }
        }

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
            $btpCategory->setMesure($this);
        }

        return $this;
    }

    public function removeBtpCategory(BtpCategorie $btpCategory): self
    {
        if ($this->btpCategories->removeElement($btpCategory)) {
            // set the owning side to null (unless already changed)
            if ($btpCategory->getMesure() === $this) {
                $btpCategory->setMesure(null);
            }
        }

        return $this;
    }
}
