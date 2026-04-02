<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * Liste les livres avec filtres optionnels (titre, auteur, catégorie).
     *
     * @return Book[]
     */
    public function search(?string $title, ?string $author, ?int $categoryId): array
    {
        $qb = $this->createQueryBuilder('b')
            ->innerJoin('b.category', 'c')->addSelect('c')
            ->innerJoin('b.language', 'l')->addSelect('l')
            ->orderBy('b.title', 'ASC');

        if ($title !== null && $title !== '') {
            $qb->andWhere('LOWER(b.title) LIKE :title')
                ->setParameter('title', '%'.mb_strtolower($title).'%');
        }

        if ($author !== null && $author !== '') {
            $qb->andWhere('LOWER(b.author) LIKE :author')
                ->setParameter('author', '%'.mb_strtolower($author).'%');
        }

        if ($categoryId !== null && $categoryId > 0) {
            $qb->andWhere('c.id = :cat')
                ->setParameter('cat', $categoryId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Book[]
     */
    public function findWhereStockAtOrBelow(int $max): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.stock <= :m')->setParameter('m', $max)
            ->orderBy('b.stock', 'ASC')
            ->setMaxResults(30)
            ->getQuery()->getResult();
    }
}
