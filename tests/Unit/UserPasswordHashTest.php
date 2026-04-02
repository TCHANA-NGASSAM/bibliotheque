<?php

namespace App\Tests\Unit;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Vérifie le chaînage hash / contrôle utilisé à la connexion (login).
 */
final class UserPasswordHashTest extends KernelTestCase
{
    public function testPasswordCanBeHashedAndVerified(): void
    {
        self::bootKernel();
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        $user = (new User())->setEmail('test-login@example.dev');
        $plain = 'motdepasse-long-8';

        $user->setPassword($hasher->hashPassword($user, $plain));

        self::assertTrue($hasher->isPasswordValid($user, $plain));
        self::assertFalse($hasher->isPasswordValid($user, 'autre-chose'));
    }
}
