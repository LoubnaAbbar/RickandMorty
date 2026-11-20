<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

// Une espèce (Human, Alien, Robot...). Clé naturelle : nom.
#[ORM\Entity]
#[UniqueEntity('nom')]
#[ApiResource(
    normalizationContext: ['groups' => ['espece:lire']],
    denormalizationContext: ['groups' => ['espece:ecrire']],
)]
class Espece
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['espece:lire'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(['espece:lire', 'espece:ecrire', 'personnage:lire'])]
    private ?string $nom = null;

    /** Côté inverse : sort en IRI (simples adresses). */
    #[ORM\OneToMany(targetEntity: Personnage::class, mappedBy: 'espece')]
    #[Groups(['espece:lire'])]
    private Collection $personnages;

    public function __construct()
    {
        $this->personnages = new ArrayCollection();
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

    /** @return Collection<int, Personnage> */
    public function getPersonnages(): Collection
    {
        return $this->personnages;
    }
}
