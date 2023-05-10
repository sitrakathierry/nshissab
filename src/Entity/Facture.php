<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureRepository::class)]
class Facture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    private ?Agence $agence = null;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    private ?FactType $type = null;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    private ?FactModele $modele = null;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    private ?CltHistoClient $client = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numFact = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    private ?FactRemiseType $remiseType = null;

    #[ORM\Column(nullable: true)]
    private ?float $remiseVal = null;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    private ?FactTva $codeTva = null;

    #[ORM\Column(nullable: true)]
    private ?float $tvaVal = null;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    private ?CaisseCommande $ticketCaisse = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'facture', targetEntity: FactHistoPaiement::class)]
    private Collection $factHistoPaiements;

    #[ORM\OneToMany(mappedBy: 'facture', targetEntity: FactDetails::class)]
    private Collection $factDetails;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieu = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    public function __construct()
    {
        $this->factHistoPaiements = new ArrayCollection();
        $this->factDetails = new ArrayCollection();
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

    public function getType(): ?FactType
    {
        return $this->type;
    }

    public function setType(?FactType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getModele(): ?FactModele
    {
        return $this->modele;
    }

    public function setModele(?FactModele $modele): self
    {
        $this->modele = $modele;

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

    public function getNumFact(): ?string
    {
        return $this->numFact;
    }

    public function setNumFact(?string $numFact): self
    {
        $this->numFact = $numFact;

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

    public function getRemiseType(): ?FactRemiseType
    {
        return $this->remiseType;
    }

    public function setRemiseType(?FactRemiseType $remiseType): self
    {
        $this->remiseType = $remiseType;

        return $this;
    }

    public function getRemiseVal(): ?float
    {
        return $this->remiseVal;
    }

    public function setRemiseVal(?float $remiseVal): self
    {
        $this->remiseVal = $remiseVal;

        return $this;
    }

    public function getCodeTva(): ?FactTva
    {
        return $this->codeTva;
    }

    public function setCodeTva(?FactTva $codeTva): self
    {
        $this->codeTva = $codeTva;

        return $this;
    }

    public function getTvaVal(): ?float
    {
        return $this->tvaVal;
    }

    public function setTvaVal(?float $tvaVal): self
    {
        $this->tvaVal = $tvaVal;

        return $this;
    }

    public function getTicketCaisse(): ?CaisseCommande
    {
        return $this->ticketCaisse;
    }

    public function setTicketCaisse(?CaisseCommande $ticketCaisse): self
    {
        $this->ticketCaisse = $ticketCaisse;

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
     * @return Collection<int, FactHistoPaiement>
     */
    public function getFactHistoPaiements(): Collection
    {
        return $this->factHistoPaiements;
    }

    public function addFactHistoPaiement(FactHistoPaiement $factHistoPaiement): self
    {
        if (!$this->factHistoPaiements->contains($factHistoPaiement)) {
            $this->factHistoPaiements->add($factHistoPaiement);
            $factHistoPaiement->setFacture($this);
        }

        return $this;
    }

    public function removeFactHistoPaiement(FactHistoPaiement $factHistoPaiement): self
    {
        if ($this->factHistoPaiements->removeElement($factHistoPaiement)) {
            // set the owning side to null (unless already changed)
            if ($factHistoPaiement->getFacture() === $this) {
                $factHistoPaiement->setFacture(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FactDetails>
     */
    public function getFactDetails(): Collection
    {
        return $this->factDetails;
    }

    public function addFactDetail(FactDetails $factDetail): self
    {
        if (!$this->factDetails->contains($factDetail)) {
            $this->factDetails->add($factDetail);
            $factDetail->setFacture($this);
        }

        return $this;
    }

    public function removeFactDetail(FactDetails $factDetail): self
    {
        if ($this->factDetails->removeElement($factDetail)) {
            // set the owning side to null (unless already changed)
            if ($factDetail->getFacture() === $this) {
                $factDetail->setFacture(null);
            }
        }

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
