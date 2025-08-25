<?php

namespace App\Repository;

use App\Entity\Pelerin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pelerin>
 */
class PelerinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pelerin::class);
    }

    /**
     * @return Pelerin[]
     */
    public function findAllWithReservation(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.reservation', 'r')
            ->leftJoin('r.depart', 'd')
            ->leftJoin('d.package', 'pk')
            ->addSelect('r', 'd', 'pk')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 