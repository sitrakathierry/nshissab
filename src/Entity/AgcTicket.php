<?php

namespace App\Entity;

use App\Repository\AgcTicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgcTicketRepository::class)]
class AgcTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sousTitre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: AgcHistoTicket::class)]
    private Collection $agcHistoTickets;

    public function __construct()
    {
        $this->agcHistoTickets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getSousTitre(): ?string
    {
        return $this->sousTitre;
    }

    public function setSousTitre(?string $sousTitre): self
    {
        $this->sousTitre = $sousTitre;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * @return Collection<int, AgcHistoTicket>
     */
    public function getAgcHistoTickets(): Collection
    {
        return $this->agcHistoTickets;
    }

    public function addAgcHistoTicket(AgcHistoTicket $agcHistoTicket): self
    {
        if (!$this->agcHistoTickets->contains($agcHistoTicket)) {
            $this->agcHistoTickets->add($agcHistoTicket);
            $agcHistoTicket->setTicket($this);
        }

        return $this;
    }

    public function removeAgcHistoTicket(AgcHistoTicket $agcHistoTicket): self
    {
        if ($this->agcHistoTickets->removeElement($agcHistoTicket)) {
            // set the owning side to null (unless already changed)
            if ($agcHistoTicket->getTicket() === $this) {
                $agcHistoTicket->setTicket(null);
            }
        }

        return $this;
    }
}
