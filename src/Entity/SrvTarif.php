<?php

namespace App\Entity;

use App\Repository\SrvTarifRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SrvTarifRepository::class)]
class SrvTarif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\ManyToOne(inversedBy: 'srvTarifs')]
    private ?Service $service = null;

    #[ORM\ManyToOne(inversedBy: 'srvTarifs')]
    private ?SrvFormat $format = null;

    #[ORM\ManyToOne(inversedBy: 'srvTarifs')]
    private ?SrvDuree $duree = null;

    #[ORM\Column(nullable: true)]
    private ?float $prix = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

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

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function getFormat(): ?SrvFormat
    {
        return $this->format;
    }

    public function setFormat(?SrvFormat $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function getDuree(): ?SrvDuree
    {
        return $this->duree;
    }

    public function setDuree(?SrvDuree $duree): self
    {
        $this->duree = $duree;

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

    public function isStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(?bool $statut): self
    {
        $this->statut = $statut;

        return $this;
    }
}
