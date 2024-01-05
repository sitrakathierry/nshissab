<?php

namespace App\Entity;

use App\Repository\HistoHistoriqueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoHistoriqueRepository::class)]
class HistoHistorique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'histoHistoriques')]
    private ?HistoModule $module = null;

    #[ORM\ManyToOne(inversedBy: 'histoHistoriques')]
    private ?HistoAction $action = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateHeure = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'histoHistoriques')]
    private ?Agence $agence4 = null;

    #[ORM\ManyToOne(inversedBy: 'histoHistoriques')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModule(): ?HistoModule
    {
        return $this->module;
    }

    public function setModule(?HistoModule $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function getAction(): ?HistoAction
    {
        return $this->action;
    }

    public function setAction(?HistoAction $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getDateHeure(): ?\DateTimeInterface
    {
        return $this->dateHeure;
    }

    public function setDateHeure(?\DateTimeInterface $dateHeure): self
    {
        $this->dateHeure = $dateHeure;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function getAgence4(): ?Agence
    {
        return $this->agence4;
    }

    public function setAgence4(?Agence $agence4): self
    {
        $this->agence4 = $agence4;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
