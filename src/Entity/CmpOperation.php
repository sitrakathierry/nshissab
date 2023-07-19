<?php

namespace App\Entity;

use App\Repository\CmpOperationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CmpOperationRepository::class)]
class CmpOperation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'cmpOperations')]
    private ?Agence $agence = null;

    #[ORM\ManyToOne(inversedBy: 'cmpOperations')]
    private ?CmpBanque $banque = null;

    #[ORM\ManyToOne(inversedBy: 'cmpOperations')]
    private ?CmpCompte $compte = null;

    #[ORM\ManyToOne(inversedBy: 'cmpOperations')]
    private ?CmpCategorie $categorie = null;

    #[ORM\ManyToOne(inversedBy: 'cmpOperations')]
    private ?CmpType $type = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $numero = null;

    #[ORM\Column(nullable: true)]
    private ?float $montant = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $personne = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

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

    public function getBanque(): ?CmpBanque
    {
        return $this->banque;
    }

    public function setBanque(?CmpBanque $banque): self
    {
        $this->banque = $banque;

        return $this;
    }

    public function getCompte(): ?CmpCompte
    {
        return $this->compte;
    }

    public function setCompte(?CmpCompte $compte): self
    {
        $this->compte = $compte;

        return $this;
    }

    public function getCategorie(): ?CmpCategorie
    {
        return $this->categorie;
    }

    public function setCategorie(?CmpCategorie $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getType(): ?CmpType
    {
        return $this->type;
    }

    public function setType(?CmpType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(?float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getPersonne(): ?string
    {
        return $this->personne;
    }

    public function setPersonne(?string $personne): self
    {
        $this->personne = $personne;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
