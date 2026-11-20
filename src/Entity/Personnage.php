<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

// Un personnage de la série. Clé naturelle : rickMortyId (l'id de l'API source).
// C'est le côté propriétaire : il porte les clés étrangères et la table de jointure.
#[ORM\Entity]
#[ApiResource(
    normalizationContext: ['groups' => ['personnage:lire']],
    denormalizationContext: ['groups' => ['personnage:ecrire']],
)]
class Personnage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['personnage:lire'])]
    private ?int $id = null;

    /** Clé naturelle : sert à ne pas importer deux fois. Jamais écrite par l'API, cachée. */
    #[ORM\Column(nullable: true, unique: true)]
    private ?int $rickMortyId = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['personnage:lire', 'personnage:ecrire'])]
    private ?string $nom = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['Alive', 'Dead', 'unknown'])]
    #[Groups(['personnage:lire', 'personnage:ecrire'])]
    private ?string $statut = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['Female', 'Male', 'Genderless', 'unknown'])]
    #[Groups(['personnage:lire', 'personnage:ecrire'])]
    private ?string $genre = null;

    /** Champ "type" de la source, souvent vide (-> null). Renommé pour ne pas confondre avec @type de JSON-LD. */
    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    #[Groups(['personnage:lire', 'personnage:ecrire'])]
    private ?string $sousType = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url]
    #[Assert\Length(max: 255)]
    #[Groups(['personnage:lire', 'personnage:ecrire'])]
    private ?string $image = null;

    /** Date "created" de la source : lecture seule (pas de groupe d'écriture). */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['personnage:lire'])]
    private ?\DateTimeImmutable $creeLe = null;

    #[ORM\ManyToOne(inversedBy: 'personnages')]
    #[Groups(['personnage:lire', 'personnage:ecrire'])]
    private ?Espece $espece = null;

    #[ORM\ManyToOne(inversedBy: 'personnagesOriginaires')]
    #[Groups(['personnage:lire', 'personnage:ecrire'])]
    private ?Lieu $origine = null;

    #[ORM\ManyToOne(inversedBy: 'residents')]
    #[Groups(['personnage:lire', 'personnage:ecrire'])]
    private ?Lieu $localisation = null;

    /**
     * ManyToMany : Doctrine crée la table de jointure personnage_episode.
     * Sort en IRI : Episode n'a aucune propriété dans le groupe personnage:lire.
     *
     * @var Collection<int, Episode>
     */
    #[ORM\ManyToMany(targetEntity: Episode::class, inversedBy: 'personnages')]
    #[Groups(['personnage:lire', 'personnage:ecrire'])]
    private Collection $episodes;

    public function __construct()
    {
        $this->episodes = new ArrayCollection();
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

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getSousType(): ?string
    {
        return $this->sousType;
    }

    public function setSousType(?string $sousType): static
    {
        $this->sousType = $sousType;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getCreeLe(): ?\DateTimeImmutable
    {
        return $this->creeLe;
    }

    public function setCreeLe(?\DateTimeImmutable $creeLe): static
    {
        $this->creeLe = $creeLe;

        return $this;
    }

    public function getEspece(): ?Espece
    {
        return $this->espece;
    }

    public function setEspece(?Espece $espece): static
    {
        $this->espece = $espece;

        return $this;
    }

    public function getOrigine(): ?Lieu
    {
        return $this->origine;
    }

    public function setOrigine(?Lieu $origine): static
    {
        $this->origine = $origine;

        return $this;
    }

    public function getLocalisation(): ?Lieu
    {
        return $this->localisation;
    }

    public function setLocalisation(?Lieu $localisation): static
    {
        $this->localisation = $localisation;

        return $this;
    }

    /** @return Collection<int, Episode> */
    public function getEpisodes(): Collection
    {
        return $this->episodes;
    }

    public function addEpisode(Episode $episode): static
    {
        if (!$this->episodes->contains($episode)) {
            $this->episodes->add($episode);
        }

        return $this;
    }

    public function removeEpisode(Episode $episode): static
    {
        $this->episodes->removeElement($episode);

        return $this;
    }
}
