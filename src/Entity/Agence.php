<?php

namespace App\Entity;

use App\Repository\AgenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgenceRepository::class)]
class Agence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $region = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $code = null;

    #[ORM\Column]
    private ?int $capacite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'agences')]
    private ?AgcDevise $devise = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(nullable: true)]
    private ?int $codeAgence = null;

    #[ORM\Column]
    private ?bool $statut = null;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: User::class)]
    private Collection $users;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: AgcHistoTicket::class)]
    private Collection $agcHistoTickets;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: MenuAgence::class)]
    private Collection $menuAgences;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: PrdCategories::class)]
    private Collection $prdCategories;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: Produit::class)]
    private Collection $produits;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: PrdEntrepot::class)]
    private Collection $prdEntrepots;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: PrdMargeType::class)]
    private Collection $prdMargeTypes;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: PrdFournisseur::class)]
    private Collection $prdFournisseurs;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: PrdHistoEntrepot::class)]
    private Collection $prdHistoEntrepots;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: CaisseCommande::class)]
    private Collection $caisseCommandes;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: CltHistoClient::class)]
    private Collection $cltHistoClients;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: FactTva::class)]
    private Collection $factTvas;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: Facture::class)]
    private Collection $factures;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->agcHistoTickets = new ArrayCollection();
        $this->menuAgences = new ArrayCollection();
        $this->prdCategories = new ArrayCollection();
        $this->produits = new ArrayCollection();
        $this->prdEntrepots = new ArrayCollection();
        $this->prdMargeTypes = new ArrayCollection();
        $this->prdFournisseurs = new ArrayCollection();
        $this->prdHistoEntrepots = new ArrayCollection();
        $this->caisseCommandes = new ArrayCollection();
        $this->cltHistoClients = new ArrayCollection();
        $this->factTvas = new ArrayCollection();
        $this->factures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCapacite(): ?int
    {
        return $this->capacite;
    }

    public function setCapacite(int $capacite): self
    {
        $this->capacite = $capacite;

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getDevise(): ?AgcDevise
    {
        return $this->devise;
    }

    public function setDevise(?AgcDevise $devise): self
    {
        $this->devise = $devise;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getCodeAgence(): ?int
    {
        return $this->codeAgence;
    }

    public function setCodeAgence(?int $codeAgence): self
    {
        $this->codeAgence = $codeAgence;

        return $this;
    }

    public function isStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(bool $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setAgence($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getAgence() === $this) {
                $user->setAgence(null);
            }
        }

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
            $agcHistoTicket->setAgence($this);
        }

        return $this;
    }

    public function removeAgcHistoTicket(AgcHistoTicket $agcHistoTicket): self
    {
        if ($this->agcHistoTickets->removeElement($agcHistoTicket)) {
            // set the owning side to null (unless already changed)
            if ($agcHistoTicket->getAgence() === $this) {
                $agcHistoTicket->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MenuAgence>
     */
    public function getMenuAgences(): Collection
    {
        return $this->menuAgences;
    }

    public function addMenuAgence(MenuAgence $menuAgence): self
    {
        if (!$this->menuAgences->contains($menuAgence)) {
            $this->menuAgences->add($menuAgence);
            $menuAgence->setAgence($this);
        }

        return $this;
    }

    public function removeMenuAgence(MenuAgence $menuAgence): self
    {
        if ($this->menuAgences->removeElement($menuAgence)) {
            // set the owning side to null (unless already changed)
            if ($menuAgence->getAgence() === $this) {
                $menuAgence->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PrdCategories>
     */
    public function getPrdCategories(): Collection
    {
        return $this->prdCategories;
    }

    public function addPrdCategory(PrdCategories $prdCategory): self
    {
        if (!$this->prdCategories->contains($prdCategory)) {
            $this->prdCategories->add($prdCategory);
            $prdCategory->setAgence($this);
        }

        return $this;
    }

    public function removePrdCategory(PrdCategories $prdCategory): self
    {
        if ($this->prdCategories->removeElement($prdCategory)) {
            // set the owning side to null (unless already changed)
            if ($prdCategory->getAgence() === $this) {
                $prdCategory->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): self
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->setAgence($this);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): self
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getAgence() === $this) {
                $produit->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PrdEntrepot>
     */
    public function getPrdEntrepots(): Collection
    {
        return $this->prdEntrepots;
    }

    public function addPrdEntrepot(PrdEntrepot $prdEntrepot): self
    {
        if (!$this->prdEntrepots->contains($prdEntrepot)) {
            $this->prdEntrepots->add($prdEntrepot);
            $prdEntrepot->setAgence($this);
        }

        return $this;
    }

    public function removePrdEntrepot(PrdEntrepot $prdEntrepot): self
    {
        if ($this->prdEntrepots->removeElement($prdEntrepot)) {
            // set the owning side to null (unless already changed)
            if ($prdEntrepot->getAgence() === $this) {
                $prdEntrepot->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PrdMargeType>
     */
    public function getPrdMargeTypes(): Collection
    {
        return $this->prdMargeTypes;
    }

    public function addPrdMargeType(PrdMargeType $prdMargeType): self
    {
        if (!$this->prdMargeTypes->contains($prdMargeType)) {
            $this->prdMargeTypes->add($prdMargeType);
            $prdMargeType->setAgence($this);
        }

        return $this;
    }

    public function removePrdMargeType(PrdMargeType $prdMargeType): self
    {
        if ($this->prdMargeTypes->removeElement($prdMargeType)) {
            // set the owning side to null (unless already changed)
            if ($prdMargeType->getAgence() === $this) {
                $prdMargeType->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PrdFournisseur>
     */
    public function getPrdFournisseurs(): Collection
    {
        return $this->prdFournisseurs;
    }

    public function addPrdFournisseur(PrdFournisseur $prdFournisseur): self
    {
        if (!$this->prdFournisseurs->contains($prdFournisseur)) {
            $this->prdFournisseurs->add($prdFournisseur);
            $prdFournisseur->setAgence($this);
        }

        return $this;
    }

    public function removePrdFournisseur(PrdFournisseur $prdFournisseur): self
    {
        if ($this->prdFournisseurs->removeElement($prdFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($prdFournisseur->getAgence() === $this) {
                $prdFournisseur->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PrdHistoEntrepot>
     */
    public function getPrdHistoEntrepots(): Collection
    {
        return $this->prdHistoEntrepots;
    }

    public function addPrdHistoEntrepot(PrdHistoEntrepot $prdHistoEntrepot): self
    {
        if (!$this->prdHistoEntrepots->contains($prdHistoEntrepot)) {
            $this->prdHistoEntrepots->add($prdHistoEntrepot);
            $prdHistoEntrepot->setAgence($this);
        }

        return $this;
    }

    public function removePrdHistoEntrepot(PrdHistoEntrepot $prdHistoEntrepot): self
    {
        if ($this->prdHistoEntrepots->removeElement($prdHistoEntrepot)) {
            // set the owning side to null (unless already changed)
            if ($prdHistoEntrepot->getAgence() === $this) {
                $prdHistoEntrepot->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CaisseCommande>
     */
    public function getCaisseCommandes(): Collection
    {
        return $this->caisseCommandes;
    }

    public function addCaisseCommande(CaisseCommande $caisseCommande): self
    {
        if (!$this->caisseCommandes->contains($caisseCommande)) {
            $this->caisseCommandes->add($caisseCommande);
            $caisseCommande->setAgence($this);
        }

        return $this;
    }

    public function removeCaisseCommande(CaisseCommande $caisseCommande): self
    {
        if ($this->caisseCommandes->removeElement($caisseCommande)) {
            // set the owning side to null (unless already changed)
            if ($caisseCommande->getAgence() === $this) {
                $caisseCommande->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CltHistoClient>
     */
    public function getCltHistoClients(): Collection
    {
        return $this->cltHistoClients;
    }

    public function addCltHistoClient(CltHistoClient $cltHistoClient): self
    {
        if (!$this->cltHistoClients->contains($cltHistoClient)) {
            $this->cltHistoClients->add($cltHistoClient);
            $cltHistoClient->setAgence($this);
        }

        return $this;
    }

    public function removeCltHistoClient(CltHistoClient $cltHistoClient): self
    {
        if ($this->cltHistoClients->removeElement($cltHistoClient)) {
            // set the owning side to null (unless already changed)
            if ($cltHistoClient->getAgence() === $this) {
                $cltHistoClient->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FactTva>
     */
    public function getFactTvas(): Collection
    {
        return $this->factTvas;
    }

    public function addFactTva(FactTva $factTva): self
    {
        if (!$this->factTvas->contains($factTva)) {
            $this->factTvas->add($factTva);
            $factTva->setAgence($this);
        }

        return $this;
    }

    public function removeFactTva(FactTva $factTva): self
    {
        if ($this->factTvas->removeElement($factTva)) {
            // set the owning side to null (unless already changed)
            if ($factTva->getAgence() === $this) {
                $factTva->setAgence(null);
            }
        }

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
            $facture->setAgence($this);
        }

        return $this;
    }

    public function removeFacture(Facture $facture): self
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getAgence() === $this) {
                $facture->setAgence(null);
            }
        }

        return $this;
    }
}
