<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\BookReview;
use App\Entity\Category;
use App\Entity\Favorite;
use App\Entity\Language;
use App\Entity\Reservation;
use App\Entity\ReservationStatus;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Jeu de données pour tester toute l’application après :
 *   php bin/console doctrine:fixtures:load
 *
 * Comptes (mot de passe identique pour tous : password) :
 *   - lecteur@biblioconnect.test          → usager (catalogue, profil, résas, favoris, avis)
 *   - lecteur2@biblioconnect.test         → second usager (avis sur un autre livre)
 *   - bibliothecaire@biblioconnect.test   → ROLE_LIBRARIAN
 *   - admin@biblioconnect.test            → ROLE_ADMIN + ROLE_LIBRARIAN
 */
final class AppFixtures extends Fixture
{
    private const DEMO_PASSWORD = 'password';

    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $categories = [];
        foreach (['Roman', 'Essai', 'Jeunesse', 'Bande dessinée', 'Science-fiction'] as $name) {
            $c = (new Category())->setName($name);
            $manager->persist($c);
            $categories[$name] = $c;
        }

        $languages = [];
        foreach (['Français', 'Anglais', 'Espagnol'] as $name) {
            $l = (new Language())->setName($name);
            $manager->persist($l);
            $languages[$name] = $l;
        }

        $booksData = [
            [
                'key' => 'miserables',
                'title' => 'Les Misérables',
                'author' => 'Victor Hugo',
                'description' => "Chef-d'œuvre sur la rédemption et la justice sociale, à travers le parcours de Jean Valjean.",
                'image' => 'https://picsum.photos/seed/miserables/240/360',
                'category' => 'Roman',
                'language' => 'Français',
                'stock' => 4,
            ],
            [
                'key' => '1984',
                'title' => '1984',
                'author' => 'George Orwell',
                'description' => 'Une dystopie sur la surveillance, la propagande et la manipulation de la vérité.',
                'image' => 'https://picsum.photos/seed/orwell1984/240/360',
                'category' => 'Science-fiction',
                'language' => 'Français',
                'stock' => 6,
            ],
            [
                'key' => 'petit_prince',
                'title' => 'Le Petit Prince',
                'author' => 'Antoine de Saint-Exupéry',
                'description' => "Conte poétique sur l'amitié, la solitude et l'essentiel invisible aux yeux.",
                'image' => 'https://picsum.photos/seed/petitprince/240/360',
                'category' => 'Jeunesse',
                'language' => 'Français',
                'stock' => 10,
            ],
            [
                'key' => 'sapiens',
                'title' => 'Sapiens',
                'author' => 'Yuval Noah Harari',
                'description' => "Histoire de l'humanité, de la préhistoire aux enjeux contemporains.",
                'image' => 'https://picsum.photos/seed/sapiens/240/360',
                'category' => 'Essai',
                'language' => 'Français',
                'stock' => 1,
            ],
            [
                'key' => 'asterix',
                'title' => 'Astérix le Gaulois',
                'author' => 'Goscinny & Uderzo',
                'description' => 'Les aventures du village gaulois qui résiste à l’occupation romaine.',
                'image' => 'https://picsum.photos/seed/asterix/240/360',
                'category' => 'Bande dessinée',
                'language' => 'Français',
                'stock' => 8,
            ],
            [
                'key' => 'hobbit',
                'title' => 'The Hobbit',
                'author' => 'J.R.R. Tolkien',
                'description' => 'Le voyage de Bilbon Sacquet vers la Montagne Solitaire.',
                'image' => 'https://picsum.photos/seed/hobbit/240/360',
                'category' => 'Science-fiction',
                'language' => 'Anglais',
                'stock' => 2,
            ],
        ];

        /** @var array<string, Book> $books */
        $books = [];
        foreach ($booksData as $row) {
            $book = (new Book())
                ->setTitle($row['title'])
                ->setAuthor($row['author'])
                ->setDescription($row['description'])
                ->setImage($row['image'])
                ->setCategory($categories[$row['category']])
                ->setLanguage($languages[$row['language']])
                ->setStock($row['stock']);
            $manager->persist($book);
            $books[$row['key']] = $book;
        }

        $userSpecs = [
            ['email' => 'lecteur@biblioconnect.test', 'roles' => []],
            ['email' => 'lecteur2@biblioconnect.test', 'roles' => []],
            ['email' => 'bibliothecaire@biblioconnect.test', 'roles' => ['ROLE_LIBRARIAN']],
            ['email' => 'admin@biblioconnect.test', 'roles' => ['ROLE_ADMIN', 'ROLE_LIBRARIAN']],
        ];

        /** @var array<string, User> $users */
        $users = [];
        foreach ($userSpecs as $spec) {
            $user = (new User())
                ->setEmail($spec['email'])
                ->setRoles($spec['roles']);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, self::DEMO_PASSWORD));
            $manager->persist($user);
            $users[$spec['email']] = $user;
        }

        $manager->flush();

        $lecteur = $users['lecteur@biblioconnect.test'];
        $lecteur2 = $users['lecteur2@biblioconnect.test'];

        // Favoris (profil usager + fiche livre)
        $manager->persist((new Favorite())->setUser($lecteur)->setBook($books['1984']));
        $manager->persist((new Favorite())->setUser($lecteur)->setBook($books['petit_prince']));

        // Réservations : à traiter côté bibliothécaire + historiques
        $now = new \DateTimeImmutable('today');

        $manager->persist(
            (new Reservation())
                ->setUser($lecteur)
                ->setBook($books['miserables'])
                ->setStartAt($now->modify('+3 days'))
                ->setEndAt($now->modify('+10 days'))
                ->setStatus(ReservationStatus::Pending),
        );

        $manager->persist(
            (new Reservation())
                ->setUser($lecteur)
                ->setBook($books['asterix'])
                ->setStartAt($now->modify('+5 days'))
                ->setEndAt($now->modify('+12 days'))
                ->setStatus(ReservationStatus::Pending),
        );

        $manager->persist(
            (new Reservation())
                ->setUser($lecteur2)
                ->setBook($books['hobbit'])
                ->setStartAt($now->modify('-20 days'))
                ->setEndAt($now->modify('-13 days'))
                ->setStatus(ReservationStatus::Confirmed),
        );
        $books['hobbit']->setStock(1);

        $manager->persist(
            (new Reservation())
                ->setUser($lecteur)
                ->setBook($books['1984'])
                ->setStartAt($now->modify('-60 days'))
                ->setEndAt($now->modify('-53 days'))
                ->setStatus(ReservationStatus::Completed),
        );

        $manager->persist(
            (new Reservation())
                ->setUser($lecteur2)
                ->setBook($books['sapiens'])
                ->setStartAt($now->modify('-30 days'))
                ->setEndAt($now->modify('-23 days'))
                ->setStatus(ReservationStatus::Cancelled),
        );

        // Avis (notes + modération : un avis masqué pour l’admin)
        $reviewVisible = (new BookReview())
            ->setUser($lecteur)
            ->setBook($books['1984'])
            ->setContent('Roman toujours d’actualité, lecture intense mais indispensable.')
            ->setRating(5)
            ->setVisible(true);
        $manager->persist($reviewVisible);

        $reviewHidden = (new BookReview())
            ->setUser($lecteur2)
            ->setBook($books['petit_prince'])
            ->setContent('Contenu de test masqué par modération (à réactiver depuis l’admin).')
            ->setRating(4)
            ->setVisible(false);
        $manager->persist($reviewHidden);

        $manager->persist(
            (new BookReview())
                ->setUser($lecteur2)
                ->setBook($books['sapiens'])
                ->setContent('Vision large et accessible de l’histoire humaine.')
                ->setRating(5)
                ->setVisible(true),
        );

        $manager->flush();
    }
}
