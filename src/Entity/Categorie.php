<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
#[ApiResource]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['categorie:read', 'livre:read'])]
    private ?int $id = null;

<<<<<<< 3-CRUD-categories
    #[ORM\Column]
    #[Groups(['categorie:read', 'categorie:write', 'livre:read'])]
    private ?int $idCat = null;

=======
>>>>>>> master
    #[ORM\Column(length: 255)]
    #[Groups(['categorie:read', 'categorie:write', 'livre:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups(['categorie:read', 'categorie:write', 'livre:read'])]
    private ?string $description = null;

    /**
     * @var Collection<int, Livre>
     */
    #[ORM\ManyToMany(targetEntity: Livre::class, mappedBy: 'categorie')]
    #[Groups(['categorie:read'])]
    #[Assert\Count(min:1,max: 3,minMessage: "Un livre doit appartenir à au moins {{ limit }} catégorie.", maxMessage: "Un livre ne peut pas appartenir à plus de {{ limit }} catégories.")]
    private Collection $livres;

    public function __construct()
    {
        $this->livres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Livre>
     */
    public function getLivres(): Collection
    {
        return $this->livres;
    }

    public function addLivre(Livre $livre): static
    {
        if (!$this->livres->contains($livre)) {
            $this->livres->add($livre);
            $livre->addCategorie($this);
        }

        return $this;
    }

    public function removeLivre(Livre $livre): static
    {
        if ($this->livres->removeElement($livre)) {
            $livre->removeCategorie($this);
        }

        return $this;
    }
    
    public function __toString(): string
    {
        return $this->nom ?? '';
    }
}
