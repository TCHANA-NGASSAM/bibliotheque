<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\BookReview;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookReview>
 */
class BookReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookReview::class);
    }

    public function findOneByUserAndBook(User $user, Book $book): ?BookReview
    {
        return $this->findOneBy(['user' => $user, 'book' => $book]);
    }

    /**
     * Avis visibles + l’avis de l’utilisateur connecté (même masqué).
     *
     * @return BookReview[]
     */
    public function findDisplayedForBook(Book $book, ?User $viewer): array
    {
        $qb = $this->createQueryBuilder('br')
            ->andWhere('br.book = :b')->setParameter('b', $book)
            ->innerJoin('br.user', 'u')->addSelect('u')
            ->orderBy('br.createdAt', 'DESC');

        if ($viewer !== null) {
            $qb->andWhere('br.visible = true OR br.user = :me')->setParameter('me', $viewer);
        } else {
            $qb->andWhere('br.visible = true');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return BookReview[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('br')
            ->innerJoin('br.book', 'b')->addSelect('b')
            ->innerJoin('br.user', 'u')->addSelect('u')
            ->orderBy('br.createdAt', 'DESC')
            ->getQuery()->getResult();
    }

    public function countHidden(): int
    {
        return (int) $this->createQueryBuilder('br')
            ->select('COUNT(br.id)')
            ->andWhere('br.visible = false')
            ->getQuery()->getSingleScalarResult();
    }
}
