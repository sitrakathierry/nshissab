<?php

namespace App\Entity;

use App\Repository\AchDetailsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AchDetailsRepository::class)]
class AchDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'achDetails')]
    private ?Agence $agence = null;

    #[ORM\ManyToOne(inversedBy: 'achDetails')]
    private ?AchBonCommande $bonCommande = null;

    #[ORM\ManyToOne(inversedBy: 'achDetails')]
    private ?AchMarchandise $marchandise = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $designation = null;

    #[ORM\Column(nullable: true)]
    private ?float $quantite = null;

    #[ORM\Column(nullable: true)]
    private ?float $prix = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statutGen = null;

    #[ORM\ManyToOne(inversedBy: 'achDetails')]
    private ?AchStatut $statut = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getBonCommande(): ?AchBonCommande
    {
        return $this->bonCommande;
    }

    public function setBonCommande(?AchBonCommande $bonCommande): self
    {
        $this->bonCommande = $bonCommande;

        return $this;
    }

    public function getMarchandise(): ?AchMarchandise
    {
        return $this->marchandise;
    }

    public function setMarchandise(?AchMarchandise $marchandise): self
    {
        $this->marchandise = $marchandise;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(?float $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function isStatutGen(): ?bool
    {
        return $this->statutGen;
    }

    public function setStatutGen(?bool $statutGen): self
    {
        $this->statutGen = $statutGen;

        return $this;
    }

    public function getStatut(): ?AchStatut
    {
        return $this->statut;
    }

    public function setStatut(?AchStatut $statut): self
    {
        $this->statut = $statut;

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
}
