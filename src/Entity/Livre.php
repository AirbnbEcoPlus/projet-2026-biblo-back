<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\LivreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: LivreRepository::class)]
#[ApiResource]
#[ApiFilter(SearchFilter::class, properties: [
    'titre' => 'partial', 
    'langue' => 'exact', 
    'categories' => 'exact',
    'auteurs' => 'exact'
])]
// Filtre pour la période de date
#[ApiFilter(DateFilter::class, properties: ['dateSortie'])]
class Livre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['livre:read', 'categorie:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['livre:read', 'categorie:read'])]
    private ?string $titre = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['livre:read', 'categorie:read'])]
    private ?\DateTime $dateSortie = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['livre:read', 'categorie:read'])]
    private ?string $langue = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['livre:read', 'categorie:read'])]
    private ?string $photoCouverture = null;

    /**
     * @var Collection<int, Emprunt>
     */
    #[ORM\OneToMany(targetEntity: Emprunt::class, mappedBy: 'livre')]
    private Collection $emprunts;

    /**
     * @var Collection<int, Auteur>
     */
    #[ORM\ManyToMany(targetEntity: Auteur::class, inversedBy: 'livres')]
    private Collection $auteurs ;

    #[ORM\OneToOne(inversedBy: 'livre', cascade: ['persist', 'remove'])]
    private ?Reservation $reservation = null;

    /**
     * @var Collection<int, Categorie>
     */
    #[ORM\ManyToMany(targetEntity: Categorie::class, inversedBy: 'livres')]
    #[Groups(['livre:read'])]
    private Collection $categories;

    public function __construct()
    {
        $this->emprunts = new ArrayCollection();
        $this->auteurs = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDateSortie(): ?\DateTime
    {
        return $this->dateSortie;
    }

    public function setDateSortie(\DateTime $dateSortie): static
    {
        $this->dateSortie = $dateSortie;

        return $this;
    }

    public function getLangue(): ?string
    {
        return $this->langue;
    }

    public function setLangue(?string $langue): static
    {
        $this->langue = $langue;

        return $this;
    }

    public function getPhotoCouverture(): ?string
    {
        return $this->photoCouverture;
    }

    public function setPhotoCouverture(?string $photoCouverture): static
    {
        $this->photoCouverture = $photoCouverture;

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
            $emprunt->setLivre($this);
        }

        return $this;
    }

    public function removeEmprunt(Emprunt $emprunt): static
    {
        if ($this->emprunts->removeElement($emprunt)) {
            // set the owning side to null (unless already changed)
            if ($emprunt->getLivre() === $this) {
                $emprunt->setLivre(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Auteur>
     */
    public function getAuteurs(): Collection
    {
        return $this->auteurs;
    }

    public function addAuteur(Auteur $auteur): static
    {
        if (!$this->auteurs->contains($auteur)) {
            $this->auteurs->add($auteur);
        }

        return $this;
    }

    public function removeAuteur(Auteur $auteur): static
    {
        $this->auteurs->removeElement($auteur);

        return $this;
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): static
    {
        $this->reservation = $reservation;

        return $this;
    }

    /**
     * @return Collection<int, Categorie>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategorie(Categorie $categorie): static
    {
        if (!$this->categories->contains($categorie)) {
            $this->categories->add($categorie);
        }

        return $this;
    }

    public function removeCategorie(Categorie $categorie): static
    {
        $this->categories->removeElement($categorie);

        return $this;
    }
}
