<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

// Un épisode. Clé naturelle : rickMortyId, pris à la fin de l'URL (.../episode/1 -> 1).
#[ORM\Entity]
#[ApiResource(
    normalizationContext: ['groups' => ['episode:lire']],
    denormalizationContext: ['groups' => ['episode:ecrire']],
)]
class Episode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['episode:lire'])]
    private ?int $id = null;

    /** Clé naturelle : jamais écrite par l'API. Lisible seulement sur /episodes. */
    #[ORM\Column(nullable: true, unique: true)]
    #[Groups(['episode:lire'])]
    private ?int $rickMortyId = null;

    /** Côté inverse de Personnage::$episodes : sort en IRI. */
    #[ORM\ManyToMany(targetEntity: Personnage::class, mappedBy: 'episodes')]
    #[Groups(['episode:lire'])]
    private Collection $personnages;

    public function __construct()
    {
        $this->personnages = new ArrayCollection();
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

    /** @return Collection<int, Personnage> */
    public function getPersonnages(): Collection
    {
        return $this->personnages;
    }
}
