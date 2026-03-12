<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ApiResource]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateResa = null;

    #[ORM\OneToOne(mappedBy: 'reservation', cascade: ['persist', 'remove'])]
    private ?Livre $livre = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    private ?Adherent $adherent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateResa(): ?\DateTime
    {
        return $this->dateResa;
    }

    public function setDateResa(\DateTime $dateResa): static
    {
        $this->dateResa = $dateResa;

        return $this;
    }

    public function getLivre(): ?Livre
    {
        return $this->livre;
    }

    public function setLivre(?Livre $livre): static
    {
        // unset the owning side of the relation if necessary
        if ($livre === null && $this->livre !== null) {
            $this->livre->setReservation(null);
        }

        // set the owning side of the relation if necessary
        if ($livre !== null && $livre->getReservation() !== $this) {
            $livre->setReservation($this);
        }

        $this->livre = $livre;

        return $this;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): static
    {
        $this->adherent = $adherent;

        return $this;
    }
}
