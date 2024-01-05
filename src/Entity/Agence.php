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

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: LctBailleur::class)]
    private Collection $lctBailleurs;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: LctLocataire::class)]
    private Collection $lctLocataires;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: LctContrat::class)]
    private Collection $lctContrats;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: LctPaiement::class)]
    private Collection $lctPaiements;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: CmpBanque::class)]
    private Collection $cmpBanques;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: CmpCompte::class)]
    private Collection $cmpComptes;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: CmpOperation::class)]
    private Collection $cmpOperations;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: AchBonCommande::class)]
    private Collection $achBonCommandes;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: AchHistoPaiement::class)]
    private Collection $achHistoPaiements;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: AchMarchandise::class)]
    private Collection $achMarchandises;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: AchDetails::class)]
    private Collection $achDetails;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: PrdType::class)]
    private Collection $prdTypes;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: PrdApprovisionnement::class)]
    private Collection $prdApprovisionnements;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: IntMateriel::class)]
    private Collection $intMateriels;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: IntLibelle::class)]
    private Collection $intLibelles;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: IntMouvement::class)]
    private Collection $intMouvements;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: CaissePanier::class)]
    private Collection $caissePaniers;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: DepService::class)]
    private Collection $depServices;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: Depense::class)]
    private Collection $depenses;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: DepLibelle::class)]
    private Collection $depLibelles;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: DepDetails::class)]
    private Collection $depDetails;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: ChkCheque::class)]
    private Collection $chkCheques;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: CltSociete::class)]
    private Collection $cltSocietes;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: Client::class)]
    private Collection $clients;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: PrdDeduction::class)]
    private Collection $prdDeductions;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: ModModelePdf::class)]
    private Collection $modModelePdfs;

    #[ORM\OneToMany(mappedBy: 'agence4', targetEntity: HistoHistorique::class)]
    private Collection $histoHistoriques;

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
        $this->lctBailleurs = new ArrayCollection();
        $this->lctLocataires = new ArrayCollection();
        $this->lctContrats = new ArrayCollection();
        $this->lctPaiements = new ArrayCollection();
        $this->cmpBanques = new ArrayCollection();
        $this->cmpComptes = new ArrayCollection();
        $this->cmpOperations = new ArrayCollection();
        $this->achBonCommandes = new ArrayCollection();
        $this->achHistoPaiements = new ArrayCollection();
        $this->achMarchandises = new ArrayCollection();
        $this->achDetails = new ArrayCollection();
        $this->prdTypes = new ArrayCollection();
        $this->prdApprovisionnements = new ArrayCollection();
        $this->intMateriels = new ArrayCollection();
        $this->intLibelles = new ArrayCollection();
        $this->intMouvements = new ArrayCollection();
        $this->caissePaniers = new ArrayCollection();
        $this->depServices = new ArrayCollection();
        $this->depenses = new ArrayCollection();
        $this->depLibelles = new ArrayCollection();
        $this->depDetails = new ArrayCollection();
        $this->chkCheques = new ArrayCollection();
        $this->cltSocietes = new ArrayCollection();
        $this->clients = new ArrayCollection();
        $this->prdDeductions = new ArrayCollection();
        $this->modModelePdfs = new ArrayCollection();
        $this->histoHistoriques = new ArrayCollection();
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

    /**
     * @return Collection<int, LctBailleur>
     */
    public function getLctBailleurs(): Collection
    {
        return $this->lctBailleurs;
    }

    public function addLctBailleur(LctBailleur $lctBailleur): self
    {
        if (!$this->lctBailleurs->contains($lctBailleur)) {
            $this->lctBailleurs->add($lctBailleur);
            $lctBailleur->setAgence($this);
        }

        return $this;
    }

    public function removeLctBailleur(LctBailleur $lctBailleur): self
    {
        if ($this->lctBailleurs->removeElement($lctBailleur)) {
            // set the owning side to null (unless already changed)
            if ($lctBailleur->getAgence() === $this) {
                $lctBailleur->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LctLocataire>
     */
    public function getLctLocataires(): Collection
    {
        return $this->lctLocataires;
    }

    public function addLctLocataire(LctLocataire $lctLocataire): self
    {
        if (!$this->lctLocataires->contains($lctLocataire)) {
            $this->lctLocataires->add($lctLocataire);
            $lctLocataire->setAgence($this);
        }

        return $this;
    }

    public function removeLctLocataire(LctLocataire $lctLocataire): self
    {
        if ($this->lctLocataires->removeElement($lctLocataire)) {
            // set the owning side to null (unless already changed)
            if ($lctLocataire->getAgence() === $this) {
                $lctLocataire->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LctContrat>
     */
    public function getLctContrats(): Collection
    {
        return $this->lctContrats;
    }

    public function addLctContrat(LctContrat $lctContrat): self
    {
        if (!$this->lctContrats->contains($lctContrat)) {
            $this->lctContrats->add($lctContrat);
            $lctContrat->setAgence($this);
        }

        return $this;
    }

    public function removeLctContrat(LctContrat $lctContrat): self
    {
        if ($this->lctContrats->removeElement($lctContrat)) {
            // set the owning side to null (unless already changed)
            if ($lctContrat->getAgence() === $this) {
                $lctContrat->setAgence(null);
            }
        }

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
            $lctPaiement->setAgence($this);
        }

        return $this;
    }

    public function removeLctPaiement(LctPaiement $lctPaiement): self
    {
        if ($this->lctPaiements->removeElement($lctPaiement)) {
            // set the owning side to null (unless already changed)
            if ($lctPaiement->getAgence() === $this) {
                $lctPaiement->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CmpBanque>
     */
    public function getCmpBanques(): Collection
    {
        return $this->cmpBanques;
    }

    public function addCmpBanque(CmpBanque $cmpBanque): self
    {
        if (!$this->cmpBanques->contains($cmpBanque)) {
            $this->cmpBanques->add($cmpBanque);
            $cmpBanque->setAgence($this);
        }

        return $this;
    }

    public function removeCmpBanque(CmpBanque $cmpBanque): self
    {
        if ($this->cmpBanques->removeElement($cmpBanque)) {
            // set the owning side to null (unless already changed)
            if ($cmpBanque->getAgence() === $this) {
                $cmpBanque->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CmpCompte>
     */
    public function getCmpComptes(): Collection
    {
        return $this->cmpComptes;
    }

    public function addCmpCompte(CmpCompte $cmpCompte): self
    {
        if (!$this->cmpComptes->contains($cmpCompte)) {
            $this->cmpComptes->add($cmpCompte);
            $cmpCompte->setAgence($this);
        }

        return $this;
    }

    public function removeCmpCompte(CmpCompte $cmpCompte): self
    {
        if ($this->cmpComptes->removeElement($cmpCompte)) {
            // set the owning side to null (unless already changed)
            if ($cmpCompte->getAgence() === $this) {
                $cmpCompte->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CmpOperation>
     */
    public function getCmpOperations(): Collection
    {
        return $this->cmpOperations;
    }

    public function addCmpOperation(CmpOperation $cmpOperation): self
    {
        if (!$this->cmpOperations->contains($cmpOperation)) {
            $this->cmpOperations->add($cmpOperation);
            $cmpOperation->setAgence($this);
        }

        return $this;
    }

    public function removeCmpOperation(CmpOperation $cmpOperation): self
    {
        if ($this->cmpOperations->removeElement($cmpOperation)) {
            // set the owning side to null (unless already changed)
            if ($cmpOperation->getAgence() === $this) {
                $cmpOperation->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AchBonCommande>
     */
    public function getAchBonCommandes(): Collection
    {
        return $this->achBonCommandes;
    }

    public function addAchBonCommande(AchBonCommande $achBonCommande): self
    {
        if (!$this->achBonCommandes->contains($achBonCommande)) {
            $this->achBonCommandes->add($achBonCommande);
            $achBonCommande->setAgence($this);
        }

        return $this;
    }

    public function removeAchBonCommande(AchBonCommande $achBonCommande): self
    {
        if ($this->achBonCommandes->removeElement($achBonCommande)) {
            // set the owning side to null (unless already changed)
            if ($achBonCommande->getAgence() === $this) {
                $achBonCommande->setAgence(null);
            }
        }

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
            $achHistoPaiement->setAgence($this);
        }

        return $this;
    }

    public function removeAchHistoPaiement(AchHistoPaiement $achHistoPaiement): self
    {
        if ($this->achHistoPaiements->removeElement($achHistoPaiement)) {
            // set the owning side to null (unless already changed)
            if ($achHistoPaiement->getAgence() === $this) {
                $achHistoPaiement->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AchMarchandise>
     */
    public function getAchMarchandises(): Collection
    {
        return $this->achMarchandises;
    }

    public function addAchMarchandise(AchMarchandise $achMarchandise): self
    {
        if (!$this->achMarchandises->contains($achMarchandise)) {
            $this->achMarchandises->add($achMarchandise);
            $achMarchandise->setAgence($this);
        }

        return $this;
    }

    public function removeAchMarchandise(AchMarchandise $achMarchandise): self
    {
        if ($this->achMarchandises->removeElement($achMarchandise)) {
            // set the owning side to null (unless already changed)
            if ($achMarchandise->getAgence() === $this) {
                $achMarchandise->setAgence(null);
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
            $achDetail->setAgence($this);
        }

        return $this;
    }

    public function removeAchDetail(AchDetails $achDetail): self
    {
        if ($this->achDetails->removeElement($achDetail)) {
            // set the owning side to null (unless already changed)
            if ($achDetail->getAgence() === $this) {
                $achDetail->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PrdType>
     */
    public function getPrdTypes(): Collection
    {
        return $this->prdTypes;
    }

    public function addPrdType(PrdType $prdType): self
    {
        if (!$this->prdTypes->contains($prdType)) {
            $this->prdTypes->add($prdType);
            $prdType->setAgence($this);
        }

        return $this;
    }

    public function removePrdType(PrdType $prdType): self
    {
        if ($this->prdTypes->removeElement($prdType)) {
            // set the owning side to null (unless already changed)
            if ($prdType->getAgence() === $this) {
                $prdType->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PrdApprovisionnement>
     */
    public function getPrdApprovisionnements(): Collection
    {
        return $this->prdApprovisionnements;
    }

    public function addPrdApprovisionnement(PrdApprovisionnement $prdApprovisionnement): self
    {
        if (!$this->prdApprovisionnements->contains($prdApprovisionnement)) {
            $this->prdApprovisionnements->add($prdApprovisionnement);
            $prdApprovisionnement->setAgence($this);
        }

        return $this;
    }

    public function removePrdApprovisionnement(PrdApprovisionnement $prdApprovisionnement): self
    {
        if ($this->prdApprovisionnements->removeElement($prdApprovisionnement)) {
            // set the owning side to null (unless already changed)
            if ($prdApprovisionnement->getAgence() === $this) {
                $prdApprovisionnement->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, IntMateriel>
     */
    public function getIntMateriels(): Collection
    {
        return $this->intMateriels;
    }

    public function addIntMateriel(IntMateriel $intMateriel): self
    {
        if (!$this->intMateriels->contains($intMateriel)) {
            $this->intMateriels->add($intMateriel);
            $intMateriel->setAgence($this);
        }

        return $this;
    }

    public function removeIntMateriel(IntMateriel $intMateriel): self
    {
        if ($this->intMateriels->removeElement($intMateriel)) {
            // set the owning side to null (unless already changed)
            if ($intMateriel->getAgence() === $this) {
                $intMateriel->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, IntLibelle>
     */
    public function getIntLibelles(): Collection
    {
        return $this->intLibelles;
    }

    public function addIntLibelle(IntLibelle $intLibelle): self
    {
        if (!$this->intLibelles->contains($intLibelle)) {
            $this->intLibelles->add($intLibelle);
            $intLibelle->setAgence($this);
        }

        return $this;
    }

    public function removeIntLibelle(IntLibelle $intLibelle): self
    {
        if ($this->intLibelles->removeElement($intLibelle)) {
            // set the owning side to null (unless already changed)
            if ($intLibelle->getAgence() === $this) {
                $intLibelle->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, IntMouvement>
     */
    public function getIntMouvements(): Collection
    {
        return $this->intMouvements;
    }

    public function addIntMouvement(IntMouvement $intMouvement): self
    {
        if (!$this->intMouvements->contains($intMouvement)) {
            $this->intMouvements->add($intMouvement);
            $intMouvement->setAgence($this);
        }

        return $this;
    }

    public function removeIntMouvement(IntMouvement $intMouvement): self
    {
        if ($this->intMouvements->removeElement($intMouvement)) {
            // set the owning side to null (unless already changed)
            if ($intMouvement->getAgence() === $this) {
                $intMouvement->setAgence(null);
            }
        }

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
            $caissePanier->setAgence($this);
        }

        return $this;
    }

    public function removeCaissePanier(CaissePanier $caissePanier): self
    {
        if ($this->caissePaniers->removeElement($caissePanier)) {
            // set the owning side to null (unless already changed)
            if ($caissePanier->getAgence() === $this) {
                $caissePanier->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DepService>
     */
    public function getDepServices(): Collection
    {
        return $this->depServices;
    }

    public function addDepService(DepService $depService): self
    {
        if (!$this->depServices->contains($depService)) {
            $this->depServices->add($depService);
            $depService->setAgence($this);
        }

        return $this;
    }

    public function removeDepService(DepService $depService): self
    {
        if ($this->depServices->removeElement($depService)) {
            // set the owning side to null (unless already changed)
            if ($depService->getAgence() === $this) {
                $depService->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Depense>
     */
    public function getDepenses(): Collection
    {
        return $this->depenses;
    }

    public function addDepense(Depense $depense): self
    {
        if (!$this->depenses->contains($depense)) {
            $this->depenses->add($depense);
            $depense->setAgence($this);
        }

        return $this;
    }

    public function removeDepense(Depense $depense): self
    {
        if ($this->depenses->removeElement($depense)) {
            // set the owning side to null (unless already changed)
            if ($depense->getAgence() === $this) {
                $depense->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DepLibelle>
     */
    public function getDepLibelles(): Collection
    {
        return $this->depLibelles;
    }

    public function addDepLibelle(DepLibelle $depLibelle): self
    {
        if (!$this->depLibelles->contains($depLibelle)) {
            $this->depLibelles->add($depLibelle);
            $depLibelle->setAgence($this);
        }

        return $this;
    }

    public function removeDepLibelle(DepLibelle $depLibelle): self
    {
        if ($this->depLibelles->removeElement($depLibelle)) {
            // set the owning side to null (unless already changed)
            if ($depLibelle->getAgence() === $this) {
                $depLibelle->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DepDetails>
     */
    public function getDepDetails(): Collection
    {
        return $this->depDetails;
    }

    public function addDepDetail(DepDetails $depDetail): self
    {
        if (!$this->depDetails->contains($depDetail)) {
            $this->depDetails->add($depDetail);
            $depDetail->setAgence($this);
        }

        return $this;
    }

    public function removeDepDetail(DepDetails $depDetail): self
    {
        if ($this->depDetails->removeElement($depDetail)) {
            // set the owning side to null (unless already changed)
            if ($depDetail->getAgence() === $this) {
                $depDetail->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ChkCheque>
     */
    public function getChkCheques(): Collection
    {
        return $this->chkCheques;
    }

    public function addChkCheque(ChkCheque $chkCheque): self
    {
        if (!$this->chkCheques->contains($chkCheque)) {
            $this->chkCheques->add($chkCheque);
            $chkCheque->setAgence($this);
        }

        return $this;
    }

    public function removeChkCheque(ChkCheque $chkCheque): self
    {
        if ($this->chkCheques->removeElement($chkCheque)) {
            // set the owning side to null (unless already changed)
            if ($chkCheque->getAgence() === $this) {
                $chkCheque->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CltSociete>
     */
    public function getCltSocietes(): Collection
    {
        return $this->cltSocietes;
    }

    public function addCltSociete(CltSociete $cltSociete): self
    {
        if (!$this->cltSocietes->contains($cltSociete)) {
            $this->cltSocietes->add($cltSociete);
            $cltSociete->setAgence($this);
        }

        return $this;
    }

    public function removeCltSociete(CltSociete $cltSociete): self
    {
        if ($this->cltSocietes->removeElement($cltSociete)) {
            // set the owning side to null (unless already changed)
            if ($cltSociete->getAgence() === $this) {
                $cltSociete->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Client>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): self
    {
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
            $client->setAgence($this);
        }

        return $this;
    }

    public function removeClient(Client $client): self
    {
        if ($this->clients->removeElement($client)) {
            // set the owning side to null (unless already changed)
            if ($client->getAgence() === $this) {
                $client->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PrdDeduction>
     */
    public function getPrdDeductions(): Collection
    {
        return $this->prdDeductions;
    }

    public function addPrdDeduction(PrdDeduction $prdDeduction): self
    {
        if (!$this->prdDeductions->contains($prdDeduction)) {
            $this->prdDeductions->add($prdDeduction);
            $prdDeduction->setAgence($this);
        }

        return $this;
    }

    public function removePrdDeduction(PrdDeduction $prdDeduction): self
    {
        if ($this->prdDeductions->removeElement($prdDeduction)) {
            // set the owning side to null (unless already changed)
            if ($prdDeduction->getAgence() === $this) {
                $prdDeduction->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ModModelePdf>
     */
    public function getModModelePdfs(): Collection
    {
        return $this->modModelePdfs;
    }

    public function addModModelePdf(ModModelePdf $modModelePdf): self
    {
        if (!$this->modModelePdfs->contains($modModelePdf)) {
            $this->modModelePdfs->add($modModelePdf);
            $modModelePdf->setAgence($this);
        }

        return $this;
    }

    public function removeModModelePdf(ModModelePdf $modModelePdf): self
    {
        if ($this->modModelePdfs->removeElement($modModelePdf)) {
            // set the owning side to null (unless already changed)
            if ($modModelePdf->getAgence() === $this) {
                $modModelePdf->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HistoHistorique>
     */
    public function getHistoHistoriques(): Collection
    {
        return $this->histoHistoriques;
    }

    public function addHistoHistorique(HistoHistorique $histoHistorique): self
    {
        if (!$this->histoHistoriques->contains($histoHistorique)) {
            $this->histoHistoriques->add($histoHistorique);
            $histoHistorique->setAgence4($this);
        }

        return $this;
    }

    public function removeHistoHistorique(HistoHistorique $histoHistorique): self
    {
        if ($this->histoHistoriques->removeElement($histoHistorique)) {
            // set the owning side to null (unless already changed)
            if ($histoHistorique->getAgence4() === $this) {
                $histoHistorique->setAgence4(null);
            }
        }

        return $this;
    }
}
