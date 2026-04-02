<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Category;
use App\Entity\Language;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
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

        $manager->flush();

        $booksData = [
            [
                'title' => 'Les Misérables',
                'author' => 'Victor Hugo',
                'description' => "Chef-d'œuvre sur la rédemption et la justice sociale, à travers le parcours de Jean Valjean.",
                'image' => 'https://picsum.photos/seed/miserables/240/360',
                'category' => 'Roman',
                'language' => 'Français',
                'stock' => 4,
            ],
            [
                'title' => '1984',
                'author' => 'George Orwell',
                'description' => 'Une dystopie sur la surveillance, la propagande et la manipulation de la vérité.',
                'image' => 'https://picsum.photos/seed/orwell1984/240/360',
                'category' => 'Science-fiction',
                'language' => 'Français',
                'stock' => 6,
            ],
            [
                'title' => 'Le Petit Prince',
                'author' => 'Antoine de Saint-Exupéry',
                'description' => "Conte poétique sur l'amitié, la solitude et l'essentiel invisible aux yeux.",
                'image' => 'https://picsum.photos/seed/petitprince/240/360',
                'category' => 'Jeunesse',
                'language' => 'Français',
                'stock' => 10,
            ],
            [
                'title' => 'Sapiens',
                'author' => 'Yuval Noah Harari',
                'description' => "Histoire de l'humanité, de la préhistoire aux enjeux contemporains.",
                'image' => 'https://picsum.photos/seed/sapiens/240/360',
                'category' => 'Essai',
                'language' => 'Français',
                'stock' => 3,
            ],
            [
                'title' => 'Astérix le Gaulois',
                'author' => 'Goscinny & Uderzo',
                'description' => 'Les aventures du village gaulois qui résiste à l’occupation romaine.',
                'image' => 'https://picsum.photos/seed/asterix/240/360',
                'category' => 'Bande dessinée',
                'language' => 'Français',
                'stock' => 8,
            ],
            [
                'title' => 'The Hobbit',
                'author' => 'J.R.R. Tolkien',
                'description' => 'Le voyage de Bilbon Sacquet vers la Montagne Solitaire.',
                'image' => 'https://picsum.photos/seed/hobbit/240/360',
                'category' => 'Science-fiction',
                'language' => 'Anglais',
                'stock' => 5,
            ],
        ];

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
        }

        $users = [
            ['email' => 'lecteur@biblioconnect.test', 'roles' => [], 'plain' => 'password'],
            ['email' => 'bibliothecaire@biblioconnect.test', 'roles' => ['ROLE_LIBRARIAN'], 'plain' => 'password'],
            ['email' => 'admin@biblioconnect.test', 'roles' => ['ROLE_ADMIN', 'ROLE_LIBRARIAN'], 'plain' => 'password'],
        ];

        foreach ($users as $u) {
            $user = (new User())
                ->setEmail($u['email'])
                ->setRoles($u['roles']);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $u['plain']));
            $manager->persist($user);
        }

        $manager->flush();
    }
}
