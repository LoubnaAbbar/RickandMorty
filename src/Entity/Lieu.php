<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

// Un lieu (origine ou dernière position d'un personnage).
// Clé naturelle : rickMortyId, pris à la fin de l'URL (.../location/3 -> 3).
#[ORM\Entity]
#[ApiResource(
    normalizationContext: ['groups' => ['lieu:lire']],
    denormalizationContext: ['groups' => ['lieu:ecrire']],
)]
class Lieu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['lieu:lire'])]
    private ?int $id = null;

    /** Clé naturelle : jamais écrite par l'API, cachée. */
    #[ORM\Column(nullable: true, unique: true)]
    private ?int $rickMortyId = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['lieu:lire', 'lieu:ecrire', 'personnage:lire'])]
    private ?string $nom = null;

    /** Côté inverse de Personnage::$origine. */
    #[ORM\OneToMany(targetEntity: Personnage::class, mappedBy: 'origine')]
    #[Groups(['lieu:lire'])]
    private Collection $personnagesOriginaires;

    /** Côté inverse de Personnage::$localisation. */
    #[ORM\OneToMany(targetEntity: Personnage::class, mappedBy: 'localisation')]
    #[Groups(['lieu:lire'])]
    private Collection $residents;

    public function __construct()
    {
        $this->personnagesOriginaires = new ArrayCollection();
        $this->residents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRickMortyId(): ?int
    {
        return $this->rickMortyId;
    }

    public function setRickMortyId(int $rickMortyId): static
    {
        $this->rickMortyId = $rickMortyId;

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

    /** @return Collection<int, Personnage> */
    public function getPersonnagesOriginaires(): Collection
    {
        return $this->personnagesOriginaires;
    }

    /** @return Collection<int, Personnage> */
    public function getResidents(): Collection
    {
        return $this->residents;
    }
}
