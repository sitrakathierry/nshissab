<?php

namespace App\Entity;

use App\Repository\SavAnnulationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SavAnnulationRepository::class)]
class SavAnnulation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'savAnnulations')]
    private ?Agence $agence = null;

    #[ORM\ManyToOne(inversedBy: 'savAnnulations')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'savAnnulations')]
    private ?Facture $facture = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numFact = null;

    #[ORM\ManyToOne(inversedBy: 'savAnnulations')]
    private ?CltHistoClient $client = null;

    #[ORM\ManyToOne(inversedBy: 'savAnnulations')]
    private ?SavType $type = null;

    #[ORM\ManyToOne(inversedBy: 'savAnnulations')]
    private ?SavSpec $specification = null;

    #[ORM\ManyToOne(inversedBy: 'savAnnulations')]
    private ?SavMotif $motif = null;

    #[ORM\Column(nullable: true)]
    private ?float $pourcentage = null;

    #[ORM\Column(nullable: true)]
    private ?float $montant = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'annulation', targetEntity: SavDetails::class)]
    private Collection $savDetails;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieu = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $explication = null;

    #[ORM\ManyToOne(inversedBy: 'savAnnulations')]
    private ?CaisseCommande $caisse = null;

    public function __construct()
    {
        $this->savDetails = new ArrayCollection();
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getNumFact(): ?string
    {
        return $this->numFact;
    }

    public function setNumFact(?string $numFact): self
    {
        $this->numFact = $numFact;

        return $this;
    }

    public function getClient(): ?CltHistoClient
    {
        return $this->client;
    }

    public function setClient(?CltHistoClient $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getType(): ?SavType
    {
        return $this->type;
    }

    public function setType(?SavType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSpecification(): ?SavSpec
    {
        return $this->specification;
    }

    public function setSpecification(?SavSpec $specification): self
    {
        $this->specification = $specification;

        return $this;
    }

    public function getMotif(): ?SavMotif
    {
        return $this->motif;
    }

    public function setMotif(?SavMotif $motif): self
    {
        $this->motif = $motif;

        return $this;
    }

    public function getPourcentage(): ?float
    {
        return $this->pourcentage;
    }

    public function setPourcentage(?float $pourcentage): self
    {
        $this->pourcentage = $pourcentage;

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

    /**
     * @return Collection<int, SavDetails>
     */
    public function getSavDetails(): Collection
    {
        return $this->savDetails;
    }

    public function addSavDetail(SavDetails $savDetail): self
    {
        if (!$this->savDetails->contains($savDetail)) {
            $this->savDetails->add($savDetail);
            $savDetail->setAnnulation($this);
        }

        return $this;
    }

    public function removeSavDetail(SavDetails $savDetail): self
    {
        if ($this->savDetails->removeElement($savDetail)) {
            // set the owning side to null (unless already changed)
            if ($savDetail->getAnnulation() === $this) {
                $savDetail->setAnnulation(null);
            }
        }

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

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getExplication(): ?string
    {
        return $this->explication;
    }

    public function setExplication(?string $explication): self
    {
        $this->explication = $explication;

        return $this;
    }

    public function getCaisse(): ?CaisseCommande
    {
        return $this->caisse;
    }

    public function setCaisse(?CaisseCommande $caisse): self
    {
        $this->caisse = $caisse;

        return $this;
    }
}
