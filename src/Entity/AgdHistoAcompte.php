<?php

namespace App\Entity;

use App\Repository\AgdHistoAcompteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgdHistoAcompteRepository::class)]
class AgdHistoAcompte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'agdHistoAcomptes')]
    private ?AgdAcompte $agendaAcompte = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAgendaAcompte(): ?AgdAcompte
    {
        return $this->agendaAcompte;
    }

    public function setAgendaAcompte(?AgdAcompte $agendaAcompte): self
    {
        $this->agendaAcompte = $agendaAcompte;

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
}
