<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $quartier = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sexe = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $situation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieu_travail = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_naissance = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieu_naissance = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prefession = null;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: CltHistoClient::class)]
    private Collection $cltHistoClients;

    public function __construct()
    {
        $this->cltHistoClients = new ArrayCollection();
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

    public function getNin(): ?string
    {
        return $this->nin;
    }

    public function setNin(?string $nin): self
    {
        $this->nin = $nin;

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

    public function getQuartier(): ?string
    {
        return $this->quartier;
    }

    public function setQuartier(?string $quartier): self
    {
        $this->quartier = $quartier;

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

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(?string $sexe): self
    {
        $this->sexe = $sexe;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getSituation(): ?string
    {
        return $this->situation;
    }

    public function setSituation(?string $situation): self
    {
        $this->situation = $situation;

        return $this;
    }

    public function getLieuTravail(): ?string
    {
        return $this->lieu_travail;
    }

    public function setLieuTravail(?string $lieu_travail): self
    {
        $this->lieu_travail = $lieu_travail;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->date_naissance;
    }

    public function setDateNaissance(?\DateTimeInterface $date_naissance): self
    {
        $this->date_naissance = $date_naissance;

        return $this;
    }

    public function getLieuNaissance(): ?string
    {
        return $this->lieu_naissance;
    }

    public function setLieuNaissance(?string $lieu_naissance): self
    {
        $this->lieu_naissance = $lieu_naissance;

        return $this;
    }

    public function getPrefession(): ?string
    {
        return $this->prefession;
    }

    public function setPrefession(?string $prefession): self
    {
        $this->prefession = $prefession;

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
            $cltHistoClient->setClient($this);
        }

        return $this;
    }

    public function removeCltHistoClient(CltHistoClient $cltHistoClient): self
    {
        if ($this->cltHistoClients->removeElement($cltHistoClient)) {
            // set the owning side to null (unless already changed)
            if ($cltHistoClient->getClient() === $this) {
                $cltHistoClient->setClient(null);
            }
        }

        return $this;
    }
}
