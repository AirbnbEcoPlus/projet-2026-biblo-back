<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ReservationRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\DBAL\Types\Types;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[UniqueEntity(
    fields: ['livre'], 
    message: 'Désolé, ce livre est déjà réservé'
)]
#[ApiResource]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['reservation:read', 'adherent:read', 'livre:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['reservation:read', 'adherent:read', 'livre:read'])]
    private ?\DateTime $dateResa = null;

#[ORM\ManyToOne(targetEntity: Livre::class, inversedBy: 'reservations')]
#[ORM\JoinColumn(name: 'livre_id', referencedColumnName: 'id', unique: true)]
private ?Livre $livre = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[Groups(['reservation:read', 'adherent:read'])]
    private ?Adherent $adherent = null;

    #[ORM\Column(length: 50)]
private string $statut = 'en_attente';

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

// Reservation.php
public function setLivre(?Livre $livre): static
{
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

    public function getStatut(): string
{
    return $this->statut;
}

public function setStatut(string $statut): static
{
    $this->statut = $statut;
    return $this;
}


public function __toString(): string
{
    return 'Réservation du livre ' . $this->getLivre() . ' du ' . $this->getDateResa()->format('d/m/Y');
}
}
