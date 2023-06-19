<?php

namespace App\Entity;

use App\Repository\BtpElementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BtpElementRepository::class)]
class BtpElement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\ManyToOne(inversedBy: 'btpElements')]
    private ?BtpMesure $mesure = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\ManyToOne(inversedBy: 'btpElements')]
    private ?Agence $agence = null;

    #[ORM\OneToMany(mappedBy: 'element', targetEntity: BtpPrix::class)]
    private Collection $btpPrixes;

    public function __construct()
    {
        $this->btpPrixes = new ArrayCollection();
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

    public function getMesure(): ?BtpMesure
    {
        return $this->mesure;
    }

    public function setMesure(?BtpMesure $mesure): self
    {
        $this->mesure = $mesure;

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
     * @return Collection<int, BtpPrix>
     */
    public function getBtpPrixes(): Collection
    {
        return $this->btpPrixes;
    }

    public function addBtpPrix(BtpPrix $btpPrix): self
    {
        if (!$this->btpPrixes->contains($btpPrix)) {
            $this->btpPrixes->add($btpPrix);
            $btpPrix->setElement($this);
        }

        return $this;
    }

    public function removeBtpPrix(BtpPrix $btpPrix): self
    {
        if ($this->btpPrixes->removeElement($btpPrix)) {
            // set the owning side to null (unless already changed)
            if ($btpPrix->getElement() === $this) {
                $btpPrix->setElement(null);
            }
        }

        return $this;
    }
}
