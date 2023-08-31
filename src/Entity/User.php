<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Util\Json;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;


#[ORM\Entity(repositoryClass:UserRepository::class)]
#[ORM\Table(name: "`user`")]
#[UniqueEntity(fields : ["email"], message: "There is already an account with this email")]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    
     #[ORM\Id]
     #[ORM\GeneratedValue]
     #[ORM\Column(type: "integer")]

    private $id;

    #[ORM\Column(type: Types::STRING, length: 180, unique: true)]

    private ?string $email;


    #[ORM\Column(type: Types::JSON)]

    private ?array $roles = [];

 
    #[ORM\Column(type: Types::STRING)]

    private ?string $password;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $logo = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Agence $agence = null;

    #[ORM\Column]
    private ?bool $statut = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UsrHistoFonction::class)]
    private Collection $usrHistoFonctions;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: MenuUser::class)]
    private Collection $menuUsers;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $poste = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PrdPreferences::class)]
    private Collection $prdPreferences;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Produit::class)]
    private Collection $produits;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PrdApprovisionnement::class)]
    private Collection $prdApprovisionnements;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: CaisseCommande::class)]
    private Collection $caisseCommandes;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Facture::class)]
    private Collection $factures;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: SavAnnulation::class)]
    private Collection $savAnnulations;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UsrAbonnement::class)]
    private Collection $usrAbonnements;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ModModelePdf::class)]
    private Collection $modModelePdfs;

    public function __construct()
    {
        $this->usrHistoFonctions = new ArrayCollection();
        $this->menuUsers = new ArrayCollection();
        $this->prdPreferences = new ArrayCollection();
        $this->produits = new ArrayCollection();
        $this->prdApprovisionnements = new ArrayCollection();
        $this->caisseCommandes = new ArrayCollection();
        $this->factures = new ArrayCollection();
        $this->savAnnulations = new ArrayCollection();
        $this->usrAbonnements = new ArrayCollection();
        $this->modModelePdfs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): ?string
    {
        return (string) $this->email;
    }


    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): ?array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;

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
     * @return Collection<int, UsrHistoFonction>
     */
    public function getUsrHistoFonctions(): Collection
    {
        return $this->usrHistoFonctions;
    }

    public function addUsrHistoFonction(UsrHistoFonction $usrHistoFonction): self
    {
        if (!$this->usrHistoFonctions->contains($usrHistoFonction)) {
            $this->usrHistoFonctions->add($usrHistoFonction);
            $usrHistoFonction->setUser($this);
        }

        return $this;
    }

    public function removeUsrHistoFonction(UsrHistoFonction $usrHistoFonction): self
    {
        if ($this->usrHistoFonctions->removeElement($usrHistoFonction)) {
            // set the owning side to null (unless already changed)
            if ($usrHistoFonction->getUser() === $this) {
                $usrHistoFonction->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MenuUser>
     */
    public function getMenuUsers(): Collection
    {
        return $this->menuUsers;
    }

    public function addMenuUser(MenuUser $menuUser): self
    {
        if (!$this->menuUsers->contains($menuUser)) {
            $this->menuUsers->add($menuUser);
            $menuUser->setUser($this);
        }

        return $this;
    }

    public function removeMenuUser(MenuUser $menuUser): self
    {
        if ($this->menuUsers->removeElement($menuUser)) {
            // set the owning side to null (unless already changed)
            if ($menuUser->getUser() === $this) {
                $menuUser->setUser(null);
            }
        }

        return $this;
    }

    public function getPoste(): ?string
    {
        return $this->poste;
    }

    public function setPoste(?string $poste): self
    {
        $this->poste = $poste;

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

    /**
     * @return Collection<int, PrdPreferences>
     */
    public function getPrdPreferences(): Collection
    {
        return $this->prdPreferences;
    }

    public function addPrdPreference(PrdPreferences $prdPreference): self
    {
        if (!$this->prdPreferences->contains($prdPreference)) {
            $this->prdPreferences->add($prdPreference);
            $prdPreference->setUser($this);
        }

        return $this;
    }

    public function removePrdPreference(PrdPreferences $prdPreference): self
    {
        if ($this->prdPreferences->removeElement($prdPreference)) {
            // set the owning side to null (unless already changed)
            if ($prdPreference->getUser() === $this) {
                $prdPreference->setUser(null);
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
            $produit->setUser($this);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): self
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getUser() === $this) {
                $produit->setUser(null);
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
            $prdApprovisionnement->setUser($this);
        }

        return $this;
    }

    public function removePrdApprovisionnement(PrdApprovisionnement $prdApprovisionnement): self
    {
        if ($this->prdApprovisionnements->removeElement($prdApprovisionnement)) {
            // set the owning side to null (unless already changed)
            if ($prdApprovisionnement->getUser() === $this) {
                $prdApprovisionnement->setUser(null);
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
            $caisseCommande->setUser($this);
        }

        return $this;
    }

    public function removeCaisseCommande(CaisseCommande $caisseCommande): self
    {
        if ($this->caisseCommandes->removeElement($caisseCommande)) {
            // set the owning side to null (unless already changed)
            if ($caisseCommande->getUser() === $this) {
                $caisseCommande->setUser(null);
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
            $facture->setUser($this);
        }

        return $this;
    }

    public function removeFacture(Facture $facture): self
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getUser() === $this) {
                $facture->setUser(null);
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
            $savAnnulation->setUser($this);
        }

        return $this;
    }

    public function removeSavAnnulation(SavAnnulation $savAnnulation): self
    {
        if ($this->savAnnulations->removeElement($savAnnulation)) {
            // set the owning side to null (unless already changed)
            if ($savAnnulation->getUser() === $this) {
                $savAnnulation->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UsrAbonnement>
     */
    public function getUsrAbonnements(): Collection
    {
        return $this->usrAbonnements;
    }

    public function addUsrAbonnement(UsrAbonnement $usrAbonnement): self
    {
        if (!$this->usrAbonnements->contains($usrAbonnement)) {
            $this->usrAbonnements->add($usrAbonnement);
            $usrAbonnement->setUser($this);
        }

        return $this;
    }

    public function removeUsrAbonnement(UsrAbonnement $usrAbonnement): self
    {
        if ($this->usrAbonnements->removeElement($usrAbonnement)) {
            // set the owning side to null (unless already changed)
            if ($usrAbonnement->getUser() === $this) {
                $usrAbonnement->setUser(null);
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
            $modModelePdf->setUser($this);
        }

        return $this;
    }

    public function removeModModelePdf(ModModelePdf $modModelePdf): self
    {
        if ($this->modModelePdfs->removeElement($modModelePdf)) {
            // set the owning side to null (unless already changed)
            if ($modModelePdf->getUser() === $this) {
                $modModelePdf->setUser(null);
            }
        }

        return $this;
    }
}
