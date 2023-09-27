<?php

namespace App\Entity;

use App\Repository\AgendaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgendaRepository::class)]
class Agenda
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'agendas')]
    private ?Agence $agence = null;

    #[ORM\ManyToOne(inversedBy: 'agendas')]
    private ?AgdTypes $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $clientNom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieu = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\OneToMany(mappedBy: 'agenda', targetEntity: AgdCommentaire::class)]
    private Collection $agdCommentaires;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $heure = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $objet = null;

    #[ORM\OneToMany(mappedBy: 'agenda', targetEntity: AgdHistorique::class)]
    private Collection $agdHistoriques;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $persInterne = null;

    public function __construct()
    {
        $this->agdCommentaires = new ArrayCollection();
        $this->agdHistoriques = new ArrayCollection();
    }

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

    public function getType(): ?AgdTypes
    {
        return $this->type;
    }

    public function setType(?AgdTypes $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getClientNom(): ?string
    {
        return $this->clientNom;
    }

    public function setClientNom(?string $clientNom): self
    {
        $this->clientNom = $clientNom;

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): self
    {
        $this->lieu = $lieu;

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
     * @return Collection<int, AgdCommentaire>
     */
    public function getAgdCommentaires(): Collection
    {
        return $this->agdCommentaires;
    }

    public function addAgdCommentaire(AgdCommentaire $agdCommentaire): self
    {
        if (!$this->agdCommentaires->contains($agdCommentaire)) {
            $this->agdCommentaires->add($agdCommentaire);
            $agdCommentaire->setAgenda($this);
        }

        return $this;
    }

    public function removeAgdCommentaire(AgdCommentaire $agdCommentaire): self
    {
        if ($this->agdCommentaires->removeElement($agdCommentaire)) {
            // set the owning side to null (unless already changed)
            if ($agdCommentaire->getAgenda() === $this) {
                $agdCommentaire->setAgenda(null);
            }
        }

        return $this;
    }

    public function getHeure(): ?string
    {
        return $this->heure;
    }

    public function setHeure(?string $heure): self
    {
        $this->heure = $heure;

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

    public function getObjet(): ?string
    {
        return $this->objet;
    }

    public function setObjet(?string $objet): self
    {
        $this->objet = $objet;

        return $this;
    }

    /**
     * @return Collection<int, AgdHistorique>
     */
    public function getAgdHistoriques(): Collection
    {
        return $this->agdHistoriques;
    }

    public function addAgdHistorique(AgdHistorique $agdHistorique): self
    {
        if (!$this->agdHistoriques->contains($agdHistorique)) {
            $this->agdHistoriques->add($agdHistorique);
            $agdHistorique->setAgenda($this);
        }

        return $this;
    }

    public function removeAgdHistorique(AgdHistorique $agdHistorique): self
    {
        if ($this->agdHistoriques->removeElement($agdHistorique)) {
            // set the owning side to null (unless already changed)
            if ($agdHistorique->getAgenda() === $this) {
                $agdHistorique->setAgenda(null);
            }
        }

        return $this;
    }

    public function getPersInterne(): ?string
    {
        return $this->persInterne;
    }

    public function setPersInterne(?string $persInterne): self
    {
        $this->persInterne = $persInterne;

        return $this;
    }
}
