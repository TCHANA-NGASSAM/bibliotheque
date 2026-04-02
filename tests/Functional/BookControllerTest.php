<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BookControllerTest extends WebTestCase
{
    public function testCatalogPageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/books');

        self::assertResponseIsSuccessful();
    }

    public function testUnknownBookReturnsNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/books/999999999');

        self::assertResponseStatusCodeSame(404);
    }
}
