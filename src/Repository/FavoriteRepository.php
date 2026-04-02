<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Favorite;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Favorite>
 */
class FavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favorite::class);
    }

    public function findOneByUserAndBook(User $user, Book $book): ?Favorite
    {
        return $this->findOneBy(['user' => $user, 'book' => $book]);
    }

    /**
     * @return Favorite[]
     */
    public function findByUserOrdered(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.user = :u')->setParameter('u', $user)
            ->innerJoin('f.book', 'b')->addSelect('b')
            ->innerJoin('b.category', 'c')->addSelect('c')
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()->getResult();
    }
}
