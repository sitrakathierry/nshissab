<?php

namespace App\Entity;

use App\Repository\FactHistoPaiementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactHistoPaiementRepository::class)]
class FactHistoPaiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'factHistoPaiements')]
    private ?FactPaiement $paiement = null;

    #[ORM\ManyToOne(inversedBy: 'factHistoPaiements')]
    private ?Facture $facture = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numero = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statutPaiement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPaiement(): ?FactPaiement
    {
        return $this->paiement;
    }

    public function setPaiement(?FactPaiement $paiement): self
    {
        $this->paiement = $paiement;

        return $this;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(?Facture $facture): self
    {
        $this->facture = $facture;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getStatutPaiement(): ?string
    {
        return $this->statutPaiement;
    }

    public function setStatutPaiement(?string $statutPaiement): self
    {
        $this->statutPaiement = $statutPaiement;

        return $this;
    }
}
