<?php

namespace App\Command;

use App\Entity\Episode;
use App\Entity\Espece;
use App\Entity\Lieu;
use App\Entity\Personnage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

// Importe les personnages de rickandmortyapi.com dans la base.
// Chaque étape correspond à une règle de l'onglet "Règles ETL" du tableur :
// E (extraire), T1 à T7 (transformer), L (charger).
#[AsCommand(
    name: 'app:import-personnages',
    description: 'Importe les personnages depuis rickandmortyapi.com',
)]
class ImportPersonnagesCommand extends Command
{
    private const URL_SOURCE = 'https://rickandmortyapi.com/api/character';

    // "Mémoire de l'import" : les fiches déjà connues (en base ou créées pendant cette exécution).
    // Elle évite de créer deux fois la même espèce, le même lieu ou le même épisode,
    // car rien n'est écrit en base avant le flush final.
    /** @var array<string, Espece> */
    private array $especes = [];
    /** @var array<int, Lieu> */
    private array $lieux = [];
    /** @var array<int, Episode> */
    private array $episodes = [];
    /** @var array<int, true> ids de la source déjà présents en base */
    private array $dejaImportes = [];

    public function __construct(
        private HttpClientInterface $client,
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        // Option facultative : --pages=3 n'importe que les 3 premières pages (60 personnages).
        // Sans option, on importe tout.
        $this->addOption('pages', null, InputOption::VALUE_REQUIRED, 'Nombre maximum de pages à importer (20 personnages par page)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->chargerMemoire();

        $maxPages = $input->getOption('pages');
        $maxPages = $maxPages === null ? null : (int) $maxPages;

        $importes = 0;
        $ignores = 0;
        $existants = 0;
        $page = 0;

        // E - Extraire : on lit une page, puis on suit "info.next" jusqu'à ce qu'il soit null.
        $url = self::URL_SOURCE;
        while ($url !== null && ($maxPages === null || $page < $maxPages)) {
            ++$page;

            try {
                usleep(300000); // petite pause entre deux pages, pour ne pas saturer la source
                for ($essai = 0; $essai < 10; ++$essai) {
                    $reponse = $this->client->request('GET', $url);
                    if ($reponse->getStatusCode() !== 429) {
                        break;
                    }
                    $io->writeln('Source saturée (429), pause de 5 secondes...');
                    sleep(5);
                }
                $donnees = $reponse->toArray();
            } catch (ExceptionInterface $e) {
                $io->error('Impossible de lire la source (' . $url . ') : ' . $e->getMessage());

                return Command::FAILURE;
            }

            foreach ($donnees['results'] ?? [] as $item) {
                $resultat = $this->traiterPersonnage($item);
                match ($resultat) {
                    'importe' => ++$importes,
                    'existant' => ++$existants,
                    default => ++$ignores,
                };
            }

            $io->writeln(sprintf('Page %d lue', $page));
            $url = $donnees['info']['next'] ?? null;
        }

        // L - Charger : tout est écrit en base en une seule fois.
        $this->em->flush();

        $io->success(sprintf(
            '%d importés, %d déjà en base, %d ignorés.',
            $importes,
            $existants,
            $ignores,
        ));

        return Command::SUCCESS;
    }

    /** @return 'importe'|'existant'|'ignore' */
    private function traiterPersonnage(array $item): string
    {
        $id = $item['id'] ?? null;
        $nom = $this->texte($item['name'] ?? null, 255); // T6 (trim) et T7 (coupe à 255)

        // T1 - Filtrer : sans id ou sans nom, le personnage est inutilisable.
        if (!is_int($id) || $nom === null) {
            return 'ignore';
        }

        // T2 - Dédoublonner : si l'id de la source est déjà en base, on passe.
        if (isset($this->dejaImportes[$id])) {
            return 'existant';
        }
        $this->dejaImportes[$id] = true;

        $personnage = (new Personnage())
            ->setRickMortyId($id)
            ->setNom($nom)
            ->setStatut($this->texte($item['status'] ?? null, 20) ?? 'unknown')
            ->setGenre($this->texte($item['gender'] ?? null, 20) ?? 'unknown')
            // T6 - Normaliser : un "type" ou une "image" vide devient null.
            ->setSousType($this->texte($item['type'] ?? null, 100))
            ->setImage($this->texte($item['image'] ?? null, 255))
            // T6 - Normaliser : la date de la source est du texte, on en fait une vraie date.
            ->setCreeLe($this->versDate($item['created'] ?? null))
            // T3 - Relier l'espèce.
            ->setEspece($this->trouverOuCreerEspece($item['species'] ?? null))
            // T4 - Relier les lieux (origine et localisation pointent vers la même table).
            ->setOrigine($this->trouverOuCreerLieu($item['origin'] ?? []))
            ->setLocalisation($this->trouverOuCreerLieu($item['location'] ?? []));

        // T5 - Relier les épisodes.
        foreach ($item['episode'] ?? [] as $urlEpisode) {
            $episode = $this->trouverOuCreerEpisode($urlEpisode);
            if ($episode !== null) {
                $personnage->addEpisode($episode);
            }
        }

        // L - persist = "je pose la fiche dans le chariot", le flush final livre tout.
        $this->em->persist($personnage);

        return 'importe';
    }

    // T3 - Une espèce = une seule fiche. Clé : son nom.
    private function trouverOuCreerEspece(mixed $nom): ?Espece
    {
        $nom = $this->texte($nom, 100);
        if ($nom === null) {
            return null; // espèce vide : le personnage n'a pas d'espèce
        }

        if (!isset($this->especes[$nom])) {
            $espece = (new Espece())->setNom($nom);
            $this->em->persist($espece);
            $this->especes[$nom] = $espece;
        }

        return $this->especes[$nom];
    }

    // T4 - Un lieu = une seule fiche. Clé : le numéro à la fin de l'URL.
    private function trouverOuCreerLieu(mixed $lieu): ?Lieu
    {
        if (!is_array($lieu)) {
            return null;
        }

        $id = $this->idDepuisUrl($lieu['url'] ?? null);
        if ($id === null) {
            return null; // URL vide ("unknown") : pas de lieu
        }

        if (!isset($this->lieux[$id])) {
            $nouveau = (new Lieu())
                ->setRickMortyId($id)
                ->setNom($this->texte($lieu['name'] ?? null, 255) ?? 'unknown');
            $this->em->persist($nouveau);
            $this->lieux[$id] = $nouveau;
        }

        return $this->lieux[$id];
    }

    // T5 - Un épisode = une seule fiche. Clé : le numéro à la fin de l'URL.
    private function trouverOuCreerEpisode(mixed $url): ?Episode
    {
        $id = $this->idDepuisUrl($url);
        if ($id === null) {
            return null;
        }

        if (!isset($this->episodes[$id])) {
            $nouveau = (new Episode())->setRickMortyId($id);
            $this->em->persist($nouveau);
            $this->episodes[$id] = $nouveau;
        }

        return $this->episodes[$id];
    }

    // ".../location/3" donne 3. Retourne null si l'URL est vide ou ne finit pas par un nombre.
    private function idDepuisUrl(mixed $url): ?int
    {
        if (!is_string($url) || trim($url) === '') {
            return null;
        }

        $fin = basename(trim($url));

        return ctype_digit($fin) ? (int) $fin : null;
    }

    // T6 et T7 - trim, chaîne vide devient null, coupe à la longueur de la colonne.
    private function texte(mixed $valeur, int $max): ?string
    {
        if (!is_string($valeur)) {
            return null;
        }

        $valeur = trim($valeur);

        return $valeur === '' ? null : mb_substr($valeur, 0, $max);
    }

    private function versDate(mixed $valeur): ?\DateTimeImmutable
    {
        if (!is_string($valeur) || trim($valeur) === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($valeur);
        } catch (\Exception) {
            return null;
        }
    }

    // On charge ce qui est déjà en base pour pouvoir relancer l'import sans rien dupliquer.
    private function chargerMemoire(): void
    {
        foreach ($this->em->getRepository(Espece::class)->findAll() as $espece) {
            $this->especes[$espece->getNom()] = $espece;
        }
        foreach ($this->em->getRepository(Lieu::class)->findAll() as $lieu) {
            if ($lieu->getRickMortyId() !== null) {
                $this->lieux[$lieu->getRickMortyId()] = $lieu;
            }
        }
        foreach ($this->em->getRepository(Episode::class)->findAll() as $episode) {
            if ($episode->getRickMortyId() !== null) {
                $this->episodes[$episode->getRickMortyId()] = $episode;
            }
        }

        $ids = $this->em->getRepository(Personnage::class)->createQueryBuilder('p')
            ->select('p.rickMortyId')
            ->where('p.rickMortyId IS NOT NULL')
            ->getQuery()
            ->getSingleColumnResult();
        $this->dejaImportes = array_fill_keys($ids, true);
    }
}