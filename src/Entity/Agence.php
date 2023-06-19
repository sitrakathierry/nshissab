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

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: Devise::class)]
    private Collection $devises;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: ParamTvaType::class)]
    private Collection $paramTvaTypes;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: CmdBonCommande::class)]
    private Collection $cmdBonCommandes;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: LvrLivraison::class)]
    private Collection $lvrLivraisons;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: LvrDetails::class)]
    private Collection $lvrDetails;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: SavAnnulation::class)]
    private Collection $savAnnulations;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: SavMotif::class)]
    private Collection $savMotifs;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: SavDetails::class)]
    private Collection $savDetails;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: CrdFinance::class)]
    private Collection $crdFinances;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: CrdDetails::class)]
    private Collection $crdDetails;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: Service::class)]
    private Collection $services;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: Agenda::class)]
    private Collection $agendas;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: AgdEcheance::class)]
    private Collection $agdEcheances;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: AgdAcompte::class)]
    private Collection $agdAcomptes;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: BtpMesure::class)]
    private Collection $btpMesures;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: BtpElement::class)]
    private Collection $btpElements;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: BtpEnoncee::class)]
    private Collection $btpEnoncees;

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
        $this->devises = new ArrayCollection();
        $this->paramTvaTypes = new ArrayCollection();
        $this->cmdBonCommandes = new ArrayCollection();
        $this->lvrLivraisons = new ArrayCollection();
        $this->lvrDetails = new ArrayCollection();
        $this->savAnnulations = new ArrayCollection();
        $this->savMotifs = new ArrayCollection();
        $this->savDetails = new ArrayCollection();
        $this->crdFinances = new ArrayCollection();
        $this->crdDetails = new ArrayCollection();
        $this->services = new ArrayCollection();
        $this->agendas = new ArrayCollection();
        $this->agdEcheances = new ArrayCollection();
        $this->agdAcomptes = new ArrayCollection();
        $this->btpMesures = new ArrayCollection();
        $this->btpElements = new ArrayCollection();
        $this->btpEnoncees = new ArrayCollection();
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

    /**
     * @return Collection<int, Devise>
     */
    public function getDevises(): Collection
    {
        return $this->devises;
    }

    public function addDevise(Devise $devise): self
    {
        if (!$this->devises->contains($devise)) {
            $this->devises->add($devise);
            $devise->setAgence($this);
        }

        return $this;
    }

    public function removeDevise(Devise $devise): self
    {
        if ($this->devises->removeElement($devise)) {
            // set the owning side to null (unless already changed)
            if ($devise->getAgence() === $this) {
                $devise->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ParamTvaType>
     */
    public function getParamTvaTypes(): Collection
    {
        return $this->paramTvaTypes;
    }

    public function addParamTvaType(ParamTvaType $paramTvaType): self
    {
        if (!$this->paramTvaTypes->contains($paramTvaType)) {
            $this->paramTvaTypes->add($paramTvaType);
            $paramTvaType->setAgence($this);
        }

        return $this;
    }

    public function removeParamTvaType(ParamTvaType $paramTvaType): self
    {
        if ($this->paramTvaTypes->removeElement($paramTvaType)) {
            // set the owning side to null (unless already changed)
            if ($paramTvaType->getAgence() === $this) {
                $paramTvaType->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CmdBonCommande>
     */
    public function getCmdBonCommandes(): Collection
    {
        return $this->cmdBonCommandes;
    }

    public function addCmdBonCommande(CmdBonCommande $cmdBonCommande): self
    {
        if (!$this->cmdBonCommandes->contains($cmdBonCommande)) {
            $this->cmdBonCommandes->add($cmdBonCommande);
            $cmdBonCommande->setAgence($this);
        }

        return $this;
    }

    public function removeCmdBonCommande(CmdBonCommande $cmdBonCommande): self
    {
        if ($this->cmdBonCommandes->removeElement($cmdBonCommande)) {
            // set the owning side to null (unless already changed)
            if ($cmdBonCommande->getAgence() === $this) {
                $cmdBonCommande->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LvrLivraison>
     */
    public function getLvrLivraisons(): Collection
    {
        return $this->lvrLivraisons;
    }

    public function addLvrLivraison(LvrLivraison $lvrLivraison): self
    {
        if (!$this->lvrLivraisons->contains($lvrLivraison)) {
            $this->lvrLivraisons->add($lvrLivraison);
            $lvrLivraison->setAgence($this);
        }

        return $this;
    }

    public function removeLvrLivraison(LvrLivraison $lvrLivraison): self
    {
        if ($this->lvrLivraisons->removeElement($lvrLivraison)) {
            // set the owning side to null (unless already changed)
            if ($lvrLivraison->getAgence() === $this) {
                $lvrLivraison->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LvrDetails>
     */
    public function getLvrDetails(): Collection
    {
        return $this->lvrDetails;
    }

    public function addLvrDetail(LvrDetails $lvrDetail): self
    {
        if (!$this->lvrDetails->contains($lvrDetail)) {
            $this->lvrDetails->add($lvrDetail);
            $lvrDetail->setAgence($this);
        }

        return $this;
    }

    public function removeLvrDetail(LvrDetails $lvrDetail): self
    {
        if ($this->lvrDetails->removeElement($lvrDetail)) {
            // set the owning side to null (unless already changed)
            if ($lvrDetail->getAgence() === $this) {
                $lvrDetail->setAgence(null);
            }
        }

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
            $savAnnulation->setAgence($this);
        }

        return $this;
    }

    public function removeSavAnnulation(SavAnnulation $savAnnulation): self
    {
        if ($this->savAnnulations->removeElement($savAnnulation)) {
            // set the owning side to null (unless already changed)
            if ($savAnnulation->getAgence() === $this) {
                $savAnnulation->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SavMotif>
     */
    public function getSavMotifs(): Collection
    {
        return $this->savMotifs;
    }

    public function addSavMotif(SavMotif $savMotif): self
    {
        if (!$this->savMotifs->contains($savMotif)) {
            $this->savMotifs->add($savMotif);
            $savMotif->setAgence($this);
        }

        return $this;
    }

    public function removeSavMotif(SavMotif $savMotif): self
    {
        if ($this->savMotifs->removeElement($savMotif)) {
            // set the owning side to null (unless already changed)
            if ($savMotif->getAgence() === $this) {
                $savMotif->setAgence(null);
            }
        }

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
            $savDetail->setAgence($this);
        }

        return $this;
    }

    public function removeSavDetail(SavDetails $savDetail): self
    {
        if ($this->savDetails->removeElement($savDetail)) {
            // set the owning side to null (unless already changed)
            if ($savDetail->getAgence() === $this) {
                $savDetail->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CrdFinance>
     */
    public function getCrdFinances(): Collection
    {
        return $this->crdFinances;
    }

    public function addCrdFinance(CrdFinance $crdFinance): self
    {
        if (!$this->crdFinances->contains($crdFinance)) {
            $this->crdFinances->add($crdFinance);
            $crdFinance->setAgence($this);
        }

        return $this;
    }

    public function removeCrdFinance(CrdFinance $crdFinance): self
    {
        if ($this->crdFinances->removeElement($crdFinance)) {
            // set the owning side to null (unless already changed)
            if ($crdFinance->getAgence() === $this) {
                $crdFinance->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CrdDetails>
     */
    public function getCrdDetails(): Collection
    {
        return $this->crdDetails;
    }

    public function addCrdDetail(CrdDetails $crdDetail): self
    {
        if (!$this->crdDetails->contains($crdDetail)) {
            $this->crdDetails->add($crdDetail);
            $crdDetail->setAgence($this);
        }

        return $this;
    }

    public function removeCrdDetail(CrdDetails $crdDetail): self
    {
        if ($this->crdDetails->removeElement($crdDetail)) {
            // set the owning side to null (unless already changed)
            if ($crdDetail->getAgence() === $this) {
                $crdDetail->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
            $service->setAgence($this);
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        if ($this->services->removeElement($service)) {
            // set the owning side to null (unless already changed)
            if ($service->getAgence() === $this) {
                $service->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Agenda>
     */
    public function getAgendas(): Collection
    {
        return $this->agendas;
    }

    public function addAgenda(Agenda $agenda): self
    {
        if (!$this->agendas->contains($agenda)) {
            $this->agendas->add($agenda);
            $agenda->setAgence($this);
        }

        return $this;
    }

    public function removeAgenda(Agenda $agenda): self
    {
        if ($this->agendas->removeElement($agenda)) {
            // set the owning side to null (unless already changed)
            if ($agenda->getAgence() === $this) {
                $agenda->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AgdEcheance>
     */
    public function getAgdEcheances(): Collection
    {
        return $this->agdEcheances;
    }

    public function addAgdEcheance(AgdEcheance $agdEcheance): self
    {
        if (!$this->agdEcheances->contains($agdEcheance)) {
            $this->agdEcheances->add($agdEcheance);
            $agdEcheance->setAgence($this);
        }

        return $this;
    }

    public function removeAgdEcheance(AgdEcheance $agdEcheance): self
    {
        if ($this->agdEcheances->removeElement($agdEcheance)) {
            // set the owning side to null (unless already changed)
            if ($agdEcheance->getAgence() === $this) {
                $agdEcheance->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AgdAcompte>
     */
    public function getAgdAcomptes(): Collection
    {
        return $this->agdAcomptes;
    }

    public function addAgdAcompte(AgdAcompte $agdAcompte): self
    {
        if (!$this->agdAcomptes->contains($agdAcompte)) {
            $this->agdAcomptes->add($agdAcompte);
            $agdAcompte->setAgence($this);
        }

        return $this;
    }

    public function removeAgdAcompte(AgdAcompte $agdAcompte): self
    {
        if ($this->agdAcomptes->removeElement($agdAcompte)) {
            // set the owning side to null (unless already changed)
            if ($agdAcompte->getAgence() === $this) {
                $agdAcompte->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BtpMesure>
     */
    public function getBtpMesures(): Collection
    {
        return $this->btpMesures;
    }

    public function addBtpMesure(BtpMesure $btpMesure): self
    {
        if (!$this->btpMesures->contains($btpMesure)) {
            $this->btpMesures->add($btpMesure);
            $btpMesure->setAgence($this);
        }

        return $this;
    }

    public function removeBtpMesure(BtpMesure $btpMesure): self
    {
        if ($this->btpMesures->removeElement($btpMesure)) {
            // set the owning side to null (unless already changed)
            if ($btpMesure->getAgence() === $this) {
                $btpMesure->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BtpElement>
     */
    public function getBtpElements(): Collection
    {
        return $this->btpElements;
    }

    public function addBtpElement(BtpElement $btpElement): self
    {
        if (!$this->btpElements->contains($btpElement)) {
            $this->btpElements->add($btpElement);
            $btpElement->setAgence($this);
        }

        return $this;
    }

    public function removeBtpElement(BtpElement $btpElement): self
    {
        if ($this->btpElements->removeElement($btpElement)) {
            // set the owning side to null (unless already changed)
            if ($btpElement->getAgence() === $this) {
                $btpElement->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BtpEnoncee>
     */
    public function getBtpEnoncees(): Collection
    {
        return $this->btpEnoncees;
    }

    public function addBtpEnoncee(BtpEnoncee $btpEnoncee): self
    {
        if (!$this->btpEnoncees->contains($btpEnoncee)) {
            $this->btpEnoncees->add($btpEnoncee);
            $btpEnoncee->setAgence($this);
        }

        return $this;
    }

    public function removeBtpEnoncee(BtpEnoncee $btpEnoncee): self
    {
        if ($this->btpEnoncees->removeElement($btpEnoncee)) {
            // set the owning side to null (unless already changed)
            if ($btpEnoncee->getAgence() === $this) {
                $btpEnoncee->setAgence(null);
            }
        }

        return $this;
    }
}
