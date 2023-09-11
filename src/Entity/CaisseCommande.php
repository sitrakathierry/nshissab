<?php

namespace App\Entity;

use App\Repository\CaisseCommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CaisseCommandeRepository::class)]
class CaisseCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'caisseCommandes')]
    private ?Agence $agence = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numCommande = null;

    #[ORM\Column(nullable: true)]
    private ?float $montantRecu = null;

    #[ORM\Column(nullable: true)]
    private ?float $montantPayee = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: CaissePanier::class)]
    private Collection $caissePaniers;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'caisseCommandes')]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'ticketCaisse', targetEntity: Facture::class)]
    private Collection $factures;

    #[ORM\Column(nullable: true)]
    private ?float $tva = null;

    #[ORM\ManyToOne(inversedBy: 'caisseCommandes')]
    private ?PrdMargeType $remiseType = null;

    #[ORM\Column(nullable: true)]
    private ?float $remiseValeur = null;

    #[ORM\OneToMany(mappedBy: 'caisse', targetEntity: SavAnnulation::class)]
    private Collection $savAnnulations;

    #[ORM\Column(nullable: true)]
    private ?bool $synchro = null;

    public function __construct()
    {
        $this->caissePaniers = new ArrayCollection();
        $this->factures = new ArrayCollection();
        $this->savAnnulations = new ArrayCollection();
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

    public function getNumCommande(): ?string
    {
        return $this->numCommande;
    }

    public function setNumCommande(?string $numCommande): self
    {
        $this->numCommande = $numCommande;

        return $this;
    }

    public function getMontantRecu(): ?float
    {
        return $this->montantRecu;
    }

    public function setMontantRecu(?float $montantRecu): self
    {
        $this->montantRecu = $montantRecu;

        return $this;
    }

    public function getMontantPayee(): ?float
    {
        return $this->montantPayee;
    }

    public function setMontantPayee(?float $montantPayee): self
    {
        $this->montantPayee = $montantPayee;

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
     * @return Collection<int, CaissePanier>
     */
    public function getCaissePaniers(): Collection
    {
        return $this->caissePaniers;
    }

    public function addCaissePanier(CaissePanier $caissePanier): self
    {
        if (!$this->caissePaniers->contains($caissePanier)) {
            $this->caissePaniers->add($caissePanier);
            $caissePanier->setCommande($this);
        }

        return $this;
    }

    public function removeCaissePanier(CaissePanier $caissePanier): self
    {
        if ($this->caissePaniers->removeElement($caissePanier)) {
            // set the owning side to null (unless already changed)
            if ($caissePanier->getCommande() === $this) {
                $caissePanier->setCommande(null);
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Facture>
     */
    public function getFactures(): Collection
    {
        return $this->factures;
    }

    public function addFacture(Facture $facture): self
    {
        if (!$this->factures->contains($facture)) {
            $this->factures->add($facture);
            $facture->setTicketCaisse($this);
        }

        return $this;
    }

    public function removeFacture(Facture $facture): self
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getTicketCaisse() === $this) {
                $facture->setTicketCaisse(null);
            }
        }

        return $this;
    }

    public function getTva(): ?float
    {
        return $this->tva;
    }

    public function setTva(?float $tva): self
    {
        $this->tva = $tva;

        return $this;
    }

    public function getRemiseType(): ?PrdMargeType
    {
        return $this->remiseType;
    }

    public function setRemiseType(?PrdMargeType $remiseType): self
    {
        $this->remiseType = $remiseType;

        return $this;
    }

    public function getRemiseValeur(): ?float
    {
        return $this->remiseValeur;
    }

    public function setRemiseValeur(?float $remiseValeur): self
    {
        $this->remiseValeur = $remiseValeur;

        return $this;
    }

    /**
     * @return Collection<int, SavAnnulation>
     */
    public function getSavAnnulations(): Collection
    {
        return $this->savAnnulations;
    }

    public function addSavAnnulation(SavAnnulation $savAnnulation): self
    {
        if (!$this->savAnnulations->contains($savAnnulation)) {
            $this->savAnnulations->add($savAnnulation);
            $savAnnulation->setCaisse($this);
        }

        return $this;
    }

    public function removeSavAnnulation(SavAnnulation $savAnnulation): self
    {
        if ($this->savAnnulations->removeElement($savAnnulation)) {
            // set the owning side to null (unless already changed)
            if ($savAnnulation->getCaisse() === $this) {
                $savAnnulation->setCaisse(null);
            }
        }

        return $this;
    }

    public function isSynchro(): ?bool
    {
        return $this->synchro;
    }

    public function setSynchro(?bool $synchro): self
    {
        $this->synchro = $synchro;

        return $this;
    }
}
