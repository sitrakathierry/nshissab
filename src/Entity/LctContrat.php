<?php

namespace App\Entity;

use App\Repository\LctContratRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LctContratRepository::class)]
class LctContrat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lctContrats')]
    private ?Agence $agence = null;

    #[ORM\ManyToOne(inversedBy: 'lctContrats')]
    private ?LctBailleur $bailleur = null;

    #[ORM\ManyToOne(inversedBy: 'lctContrats')]
    private ?LctBail $bail = null;

    #[ORM\ManyToOne(inversedBy: 'lctContrats')]
    private ?LctLocataire $locataire = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numContrat = null;

    #[ORM\Column(nullable: true)]
    private ?float $montantContrat = null;

    #[ORM\ManyToOne(inversedBy: 'lctContrats')]
    private ?LctCycle $cycle = null;

    #[ORM\ManyToOne(inversedBy: 'lctContrats')]
    private ?LctForfait $forfait = null;

    #[ORM\Column(nullable: true)]
    private ?float $montantForfait = null;

    #[ORM\Column(nullable: true)]
    private ?int $duree = null;

    #[ORM\ManyToOne(inversedBy: 'lctContrats')]
    private ?LctPeriode $periode = null;

    #[ORM\Column(nullable: true)]
    private ?float $pourcentage = null;

    #[ORM\ManyToOne(inversedBy: 'lctContrats')]
    private ?LctRenouvellement $renouvellement = null;

    #[ORM\ManyToOne(inversedBy: 'lctContrats')]
    private ?LctTypeLocation $typeLocation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\ManyToOne(inversedBy: 'lctContrats')]
    private ?LctModePaiement $modePaiement = null;

    #[ORM\Column(nullable: true)]
    private ?int $dateLimite = null;

    #[ORM\Column(nullable: true)]
    private ?float $caution = null;

    #[ORM\Column(nullable: true)]
    private ?int $delaiChgFin = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieuContrat = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateContrat = null;

    #[ORM\ManyToOne(inversedBy: 'lctContrats')]
    private ?LctStatut $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $captionRenouv = null;

    #[ORM\OneToMany(mappedBy: 'contrat', targetEntity: LctPaiement::class)]
    private Collection $lctPaiements;

    #[ORM\OneToMany(mappedBy: 'contrat', targetEntity: LctRepartition::class)]
    private Collection $lctRepartitions;

    #[ORM\Column(nullable: true)]
    private ?bool $statutGen = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'lctContrats')]
    private ?self $ctrParent = null;

    #[ORM\OneToMany(mappedBy: 'ctrParent', targetEntity: self::class)]
    private Collection $lctContrats;

    public function __construct()
    {
        $this->lctPaiements = new ArrayCollection();
        $this->lctRepartitions = new ArrayCollection();
        $this->lctContrats = new ArrayCollection();
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

    public function getBailleur(): ?LctBailleur
    {
        return $this->bailleur;
    }

    public function setBailleur(?LctBailleur $bailleur): self
    {
        $this->bailleur = $bailleur;

        return $this;
    }

    public function getBail(): ?LctBail
    {
        return $this->bail;
    }

    public function setBail(?LctBail $bail): self
    {
        $this->bail = $bail;

        return $this;
    }

    public function getLocataire(): ?LctLocataire
    {
        return $this->locataire;
    }

    public function setLocataire(?LctLocataire $locataire): self
    {
        $this->locataire = $locataire;

        return $this;
    }

    public function getNumContrat(): ?string
    {
        return $this->numContrat;
    }

    public function setNumContrat(?string $numContrat): self
    {
        $this->numContrat = $numContrat;

        return $this;
    }

    public function getMontantContrat(): ?float
    {
        return $this->montantContrat;
    }

    public function setMontantContrat(?float $montantContrat): self
    {
        $this->montantContrat = $montantContrat;

        return $this;
    }

    public function getCycle(): ?LctCycle
    {
        return $this->cycle;
    }

    public function setCycle(?LctCycle $cycle): self
    {
        $this->cycle = $cycle;

        return $this;
    }

    public function getForfait(): ?LctForfait
    {
        return $this->forfait;
    }

    public function setForfait(?LctForfait $forfait): self
    {
        $this->forfait = $forfait;

        return $this;
    }

    public function getMontantForfait(): ?float
    {
        return $this->montantForfait;
    }

    public function setMontantForfait(?float $montantForfait): self
    {
        $this->montantForfait = $montantForfait;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getPeriode(): ?LctPeriode
    {
        return $this->periode;
    }

    public function setPeriode(?LctPeriode $periode): self
    {
        $this->periode = $periode;

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

    public function getRenouvellement(): ?LctRenouvellement
    {
        return $this->renouvellement;
    }

    public function setRenouvellement(?LctRenouvellement $renouvellement): self
    {
        $this->renouvellement = $renouvellement;

        return $this;
    }

    public function getTypeLocation(): ?LctTypeLocation
    {
        return $this->typeLocation;
    }

    public function setTypeLocation(?LctTypeLocation $typeLocation): self
    {
        $this->typeLocation = $typeLocation;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getModePaiement(): ?LctModePaiement
    {
        return $this->modePaiement;
    }

    public function setModePaiement(?LctModePaiement $modePaiement): self
    {
        $this->modePaiement = $modePaiement;

        return $this;
    }

    public function getDateLimite(): ?int
    {
        return $this->dateLimite;
    }

    public function setDateLimite(?int $dateLimite): self
    {
        $this->dateLimite = $dateLimite;

        return $this;
    }

    public function getCaution(): ?float
    {
        return $this->caution;
    }

    public function setCaution(?float $caution): self
    {
        $this->caution = $caution;

        return $this;
    }

    public function getDelaiChgFin(): ?int
    {
        return $this->delaiChgFin;
    }

    public function setDelaiChgFin(?int $delaiChgFin): self
    {
        $this->delaiChgFin = $delaiChgFin;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getLieuContrat(): ?string
    {
        return $this->lieuContrat;
    }

    public function setLieuContrat(?string $lieuContrat): self
    {
        $this->lieuContrat = $lieuContrat;

        return $this;
    }

    public function getDateContrat(): ?\DateTimeInterface
    {
        return $this->dateContrat;
    }

    public function setDateContrat(?\DateTimeInterface $dateContrat): self
    {
        $this->dateContrat = $dateContrat;

        return $this;
    }

    public function getStatut(): ?LctStatut
    {
        return $this->statut;
    }

    public function setStatut(?LctStatut $statut): self
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

    public function getCaptionRenouv(): ?string
    {
        return $this->captionRenouv;
    }

    public function setCaptionRenouv(?string $captionRenouv): self
    {
        $this->captionRenouv = $captionRenouv;

        return $this;
    }

    /**
     * @return Collection<int, LctPaiement>
     */
    public function getLctPaiements(): Collection
    {
        return $this->lctPaiements;
    }

    public function addLctPaiement(LctPaiement $lctPaiement): self
    {
        if (!$this->lctPaiements->contains($lctPaiement)) {
            $this->lctPaiements->add($lctPaiement);
            $lctPaiement->setContrat($this);
        }

        return $this;
    }

    public function removeLctPaiement(LctPaiement $lctPaiement): self
    {
        if ($this->lctPaiements->removeElement($lctPaiement)) {
            // set the owning side to null (unless already changed)
            if ($lctPaiement->getContrat() === $this) {
                $lctPaiement->setContrat(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LctRepartition>
     */
    public function getLctRepartitions(): Collection
    {
        return $this->lctRepartitions;
    }

    public function addLctRepartition(LctRepartition $lctRepartition): self
    {
        if (!$this->lctRepartitions->contains($lctRepartition)) {
            $this->lctRepartitions->add($lctRepartition);
            $lctRepartition->setContrat($this);
        }

        return $this;
    }

    public function removeLctRepartition(LctRepartition $lctRepartition): self
    {
        if ($this->lctRepartitions->removeElement($lctRepartition)) {
            // set the owning side to null (unless already changed)
            if ($lctRepartition->getContrat() === $this) {
                $lctRepartition->setContrat(null);
            }
        }

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

    public function getCtrParent(): ?self
    {
        return $this->ctrParent;
    }

    public function setCtrParent(?self $ctrParent): self
    {
        $this->ctrParent = $ctrParent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getLctContrats(): Collection
    {
        return $this->lctContrats;
    }

    public function addLctContrat(self $lctContrat): self
    {
        if (!$this->lctContrats->contains($lctContrat)) {
            $this->lctContrats->add($lctContrat);
            $lctContrat->setCtrParent($this);
        }

        return $this;
    }

    public function removeLctContrat(self $lctContrat): self
    {
        if ($this->lctContrats->removeElement($lctContrat)) {
            // set the owning side to null (unless already changed)
            if ($lctContrat->getCtrParent() === $this) {
                $lctContrat->setCtrParent(null);
            }
        }

        return $this;
    }
}
