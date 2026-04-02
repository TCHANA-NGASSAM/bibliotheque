<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\ReservationStatus;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * @return Reservation[]
     */
    public function findByUserOrdered(User $user): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :u')->setParameter('u', $user)
            ->innerJoin('r.book', 'b')->addSelect('b')
            ->orderBy('r.startAt', 'DESC')
            ->getQuery()->getResult();
    }

    /**
     * @return Reservation[]
     */
    public function findAllForStaffOrdered(): array
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.user', 'u')->addSelect('u')
            ->innerJoin('r.book', 'b')->addSelect('b')
            ->orderBy('r.startAt', 'DESC')
            ->getQuery()->getResult();
    }

    public function countPending(): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.status = :p')->setParameter('p', ReservationStatus::Pending)
            ->getQuery()->getSingleScalarResult();
    }
}
