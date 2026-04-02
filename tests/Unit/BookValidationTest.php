<?php

namespace App\Tests\Unit;

use App\Entity\Book;
use App\Entity\Category;
use App\Entity\Language;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class BookValidationTest extends KernelTestCase
{
    public function testNegativeStockIsInvalid(): void
    {
        self::bootKernel();
        $validator = self::getContainer()->get(ValidatorInterface::class);

        $book = (new Book())
            ->setTitle('Titre')
            ->setAuthor('Auteur')
            ->setDescription('Description')
            ->setImage('https://example.com/x.jpg')
            ->setCategory((new Category())->setName('Cat'))
            ->setLanguage((new Language())->setName('Fr'))
            ->setStock(-1);

        $violations = $validator->validate($book);
        self::assertGreaterThan(0, $violations->count(), 'Un stock négatif doit être rejeté.');
    }
}
