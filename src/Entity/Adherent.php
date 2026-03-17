<?php

namespace App\Entity;

use App\Repository\AdherentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AdherentRepository::class)]
class Adherent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['adherent:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['adherent:read', 'reservation:read', 'emprunt:read'])]
    private ?\DateTime $dateAdhesion = null;

    #[ORM\Column(length: 255)]
    #[Groups(['adherent:read', 'reservation:read', 'emprunt:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups(['adherent:read', 'reservation:read', 'emprunt:read'])]
    private ?string $prenom = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['adherent:read'])]
    private ?\DateTime $dateNaiss = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['adherent:read'])]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['adherent:read'])]
    private ?string $adressePostale = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['adherent:read'])]
    private ?string $numTel = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['adherent:read', 'reservation:read', 'emprunt:read'])]
    private ?string $photo = null;

    /**
     * @var Collection<int, Emprunt>
     */
    #[ORM\OneToMany(targetEntity: Emprunt::class, mappedBy: 'adherent')]
    #[Groups(['adherent:read'])]

    private Collection $emprunts;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'adherent')]
    #[Assert\Count(max: 3,maxMessage: "Un adhérent ne peut pas avoir plus de {{ limit }} réservations.")]
    #[Groups(['adherent:read'])]
    private Collection $reservations;

    #[ORM\OneToOne(mappedBy: 'adherent')]
    private ?Utilisateur $utilisateur = null;

    public function __construct()
    {
        $this->emprunts = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateAdhesion(): ?\DateTime
    {
        return $this->dateAdhesion;
    }

    public function setDateAdhesion(\DateTime $dateAdhesion): static
    {
        $this->dateAdhesion = $dateAdhesion;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }   

    public function getDateNaiss(): ?\DateTime
    {
        return $this->dateNaiss;
    }

    public function setDateNaiss(\DateTime $dateNaiss): static
    {
        $this->dateNaiss = $dateNaiss;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAdressePostale(): ?string
    {
        return $this->adressePostale;
    }

    public function setAdressePostale(?string $adressePostale): static
    {
        $this->adressePostale = $adressePostale;

        return $this;
    }

    public function getNumTel(): ?string
    {
        return $this->numTel;
    }

    public function setNumTel(?string $numTel): static
    {
        $this->numTel = $numTel;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * @return Collection<int, Emprunt>
     */
    public function getEmprunts(): Collection
    {
        return $this->emprunts;
    }

    public function addEmprunt(Emprunt $emprunt): static
    {
        if (!$this->emprunts->contains($emprunt)) {
            $this->emprunts->add($emprunt);
            $emprunt->setAdherent($this);
        }

        return $this;
    }

    public function removeEmprunt(Emprunt $emprunt): static
    {
        if ($this->emprunts->removeElement($emprunt)) {
            // set the owning side to null (unless already changed)
            if ($emprunt->getAdherent() === $this) {
                $emprunt->setAdherent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setAdherent($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getAdherent() === $this) {
                $reservation->setAdherent(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return trim(($this->getPrenom() ?? '').' '.($this->getNom() ?? ''));
    }
}
