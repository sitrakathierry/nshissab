<?php

namespace App\Entity;

use App\Repository\AgdAcompteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgdAcompteRepository::class)]
class AgdAcompte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'agdAcomptes')]
    private ?CrdFinance $acompte = null;

    #[ORM\Column(length: 300, nullable: true)]
    private ?string $objet = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\OneToMany(mappedBy: 'agendaAcompte', targetEntity: AgdHistoAcompte::class)]
    private Collection $agdHistoAcomptes;

    #[ORM\ManyToOne(inversedBy: 'agdAcomptes')]
    private ?Agence $agence = null;

    public function __construct()
    {
        $this->agdHistoAcomptes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAcompte(): ?CrdFinance
    {
        return $this->acompte;
    }

    public function setAcompte(?CrdFinance $acompte): self
    {
        $this->acompte = $acompte;

        return $this;
    }

    public function getObjet(): ?string
    {
        return $this->objet;
    }

    public function setObjet(?string $objet): self
    {
        $this->objet = $objet;

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

    /**
     * @return Collection<int, AgdHistoAcompte>
     */
    public function getAgdHistoAcomptes(): Collection
    {
        return $this->agdHistoAcomptes;
    }

    public function addAgdHistoAcompte(AgdHistoAcompte $agdHistoAcompte): self
    {
        if (!$this->agdHistoAcomptes->contains($agdHistoAcompte)) {
            $this->agdHistoAcomptes->add($agdHistoAcompte);
            $agdHistoAcompte->setAgendaAcompte($this);
        }

        return $this;
    }

    public function removeAgdHistoAcompte(AgdHistoAcompte $agdHistoAcompte): self
    {
        if ($this->agdHistoAcomptes->removeElement($agdHistoAcompte)) {
            // set the owning side to null (unless already changed)
            if ($agdHistoAcompte->getAgendaAcompte() === $this) {
                $agdHistoAcompte->setAgendaAcompte(null);
            }
        }

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
}
