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

    public function __construct()
    {
        $this->usrHistoFonctions = new ArrayCollection();
        $this->menuUsers = new ArrayCollection();
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
        return (string) $this->email;
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
}
