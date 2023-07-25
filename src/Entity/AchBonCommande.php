<?php

namespace App\Entity;

use App\Repository\AchBonCommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AchBonCommandeRepository::class)]
class AchBonCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $numero = null;

    #[ORM\ManyToOne(inversedBy: 'achBonCommandes')]
    private ?Agence $agence = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieu = null;

    #[ORM\ManyToOne(inversedBy: 'achBonCommandes')]
    private ?PrdFournisseur $fournisseur = null;

    #[ORM\ManyToOne(inversedBy: 'achBonCommandes')]
    private ?AchType $type = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statutGen = null;

    #[ORM\ManyToOne(inversedBy: 'achBonCommandes')]
    private ?AchStatutBon $statutBon = null;

    #[ORM\ManyToOne(inversedBy: 'achBonCommandes')]
    private ?AchStatut $statut = null;

    #[ORM\Column(nullable: true)]
    private ?float $montant = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'bonCommande', targetEntity: AchHistoPaiement::class)]
    private Collection $achHistoPaiements;

    #[ORM\OneToMany(mappedBy: 'bonCommande', targetEntity: AchDetails::class)]
    private Collection $achDetails;

    public function __construct()
    {
        $this->achHistoPaiements = new ArrayCollection();
        $this->achDetails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAgence(): ?Agence
    {
        return $this->agence;
    }

    public function setAgence(?Agence $agence): self
    {
        $this->agence = $agence;

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

    public function getFournisseur(): ?PrdFournisseur
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?PrdFournisseur $fournisseur): self
    {
        $this->fournisseur = $fournisseur;

        return $this;
    }

    public function getType(): ?AchType
    {
        return $this->type;
    }

    public function setType(?AchType $type): self
    {
        $this->type = $type;

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

    public function isStatutGen(): ?bool
    {
        return $this->statutGen;
    }

    public function setStatutGen(?bool $statutGen): self
    {
        $this->statutGen = $statutGen;

        return $this;
    }

    public function getStatutBon(): ?AchStatutBon
    {
        return $this->statutBon;
    }

    public function setStatutBon(?AchStatutBon $statutBon): self
    {
        $this->statutBon = $statutBon;

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

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(?float $montant): self
    {
        $this->montant = $montant;

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
     * @return Collection<int, AchHistoPaiement>
     */
    public function getAchHistoPaiements(): Collection
    {
        return $this->achHistoPaiements;
    }

    public function addAchHistoPaiement(AchHistoPaiement $achHistoPaiement): self
    {
        if (!$this->achHistoPaiements->contains($achHistoPaiement)) {
            $this->achHistoPaiements->add($achHistoPaiement);
            $achHistoPaiement->setBonCommande($this);
        }

        return $this;
    }

    public function removeAchHistoPaiement(AchHistoPaiement $achHistoPaiement): self
    {
        if ($this->achHistoPaiements->removeElement($achHistoPaiement)) {
            // set the owning side to null (unless already changed)
            if ($achHistoPaiement->getBonCommande() === $this) {
                $achHistoPaiement->setBonCommande(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AchDetails>
     */
    public function getAchDetails(): Collection
    {
        return $this->achDetails;
    }

    public function addAchDetail(AchDetails $achDetail): self
    {
        if (!$this->achDetails->contains($achDetail)) {
            $this->achDetails->add($achDetail);
            $achDetail->setBonCommande($this);
        }

        return $this;
    }

    public function removeAchDetail(AchDetails $achDetail): self
    {
        if ($this->achDetails->removeElement($achDetail)) {
            // set the owning side to null (unless already changed)
            if ($achDetail->getBonCommande() === $this) {
                $achDetail->setBonCommande(null);
            }
        }

        return $this;
    }
}
