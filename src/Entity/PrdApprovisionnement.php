<?php

namespace App\Entity;

use App\Repository\PrdApprovisionnementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrdApprovisionnementRepository::class)]
class PrdApprovisionnement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'prdApprovisionnements')]
    private ?PrdHistoEntrepot $histoEntrepot = null;

    #[ORM\ManyToOne(inversedBy: 'prdApprovisionnements')]
    private ?PrdVariationPrix $variationPrix = null;

    #[ORM\Column(nullable: true)]
    private ?int $quantite = null;

    #[ORM\Column(nullable: true)]
    private ?int $prixAchat = null;

    #[ORM\Column(nullable: true)]
    private ?int $charge = null;

    #[ORM\ManyToOne(inversedBy: 'prdApprovisionnements')]
    private ?PrdMargeType $margeType = null;

    #[ORM\Column(nullable: true)]
    private ?int $margeValeur = null;

    #[ORM\Column(nullable: true)]
    private ?int $prixRevient = null;

    #[ORM\Column(nullable: true)]
    private ?int $prixVente = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $expiree_le = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_appro = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\OneToMany(mappedBy: 'prdAppro', targetEntity: PrdApproFournisseur::class)]
    private Collection $prdApproFournisseurs;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'prdApprovisionnements')]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'approvisionnement', targetEntity: PrdHistoFournisseur::class)]
    private Collection $prdHistoFournisseurs;

    #[ORM\ManyToOne(inversedBy: 'prdApprovisionnements')]
    private ?Agence $agence = null;

    public function __construct()
    {
        $this->prdApproFournisseurs = new ArrayCollection();
        $this->prdHistoFournisseurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHistoEntrepot(): ?PrdHistoEntrepot
    {
        return $this->histoEntrepot;
    }

    public function setHistoEntrepot(?PrdHistoEntrepot $histoEntrepot): self
    {
        $this->histoEntrepot = $histoEntrepot;

        return $this;
    }

    public function getVariationPrix(): ?PrdVariationPrix
    {
        return $this->variationPrix;
    }

    public function setVariationPrix(?PrdVariationPrix $variationPrix): self
    {
        $this->variationPrix = $variationPrix;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(?int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getPrixAchat(): ?int
    {
        return $this->prixAchat;
    }

    public function setPrixAchat(?int $prixAchat): self
    {
        $this->prixAchat = $prixAchat;

        return $this;
    }

    public function getCharge(): ?int
    {
        return $this->charge;
    }

    public function setCharge(?int $charge): self
    {
        $this->charge = $charge;

        return $this;
    }

    public function getMargeType(): ?PrdMargeType
    {
        return $this->margeType;
    }

    public function setMargeType(?PrdMargeType $margeType): self
    {
        $this->margeType = $margeType;

        return $this;
    }

    public function getMargeValeur(): ?int
    {
        return $this->margeValeur;
    }

    public function setMargeValeur(?int $margeValeur): self
    {
        $this->margeValeur = $margeValeur;

        return $this;
    }

    public function getPrixRevient(): ?int
    {
        return $this->prixRevient;
    }

    public function setPrixRevient(?int $prixRevient): self
    {
        $this->prixRevient = $prixRevient;

        return $this;
    }

    public function getPrixVente(): ?int
    {
        return $this->prixVente;
    }

    public function setPrixVente(?int $prixVente): self
    {
        $this->prixVente = $prixVente;

        return $this;
    }

    public function getExpireeLe(): ?\DateTimeInterface
    {
        return $this->expiree_le;
    }

    public function setExpireeLe(?\DateTimeInterface $expiree_le): self
    {
        $this->expiree_le = $expiree_le;

        return $this;
    }

    public function getDateAppro(): ?\DateTimeInterface
    {
        return $this->date_appro;
    }

    public function setDateAppro(?\DateTimeInterface $date_appro): self
    {
        $this->date_appro = $date_appro;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection<int, PrdApproFournisseur>
     */
    public function getPrdApproFournisseurs(): Collection
    {
        return $this->prdApproFournisseurs;
    }

    public function addPrdApproFournisseur(PrdApproFournisseur $prdApproFournisseur): self
    {
        if (!$this->prdApproFournisseurs->contains($prdApproFournisseur)) {
            $this->prdApproFournisseurs->add($prdApproFournisseur);
            $prdApproFournisseur->setPrdAppro($this);
        }

        return $this;
    }

    public function removePrdApproFournisseur(PrdApproFournisseur $prdApproFournisseur): self
    {
        if ($this->prdApproFournisseurs->removeElement($prdApproFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($prdApproFournisseur->getPrdAppro() === $this) {
                $prdApproFournisseur->setPrdAppro(null);
            }
        }

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
     * @return Collection<int, PrdHistoFournisseur>
     */
    public function getPrdHistoFournisseurs(): Collection
    {
        return $this->prdHistoFournisseurs;
    }

    public function addPrdHistoFournisseur(PrdHistoFournisseur $prdHistoFournisseur): self
    {
        if (!$this->prdHistoFournisseurs->contains($prdHistoFournisseur)) {
            $this->prdHistoFournisseurs->add($prdHistoFournisseur);
            $prdHistoFournisseur->setApprovisionnement($this);
        }

        return $this;
    }

    public function removePrdHistoFournisseur(PrdHistoFournisseur $prdHistoFournisseur): self
    {
        if ($this->prdHistoFournisseurs->removeElement($prdHistoFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($prdHistoFournisseur->getApprovisionnement() === $this) {
                $prdHistoFournisseur->setApprovisionnement(null);
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
