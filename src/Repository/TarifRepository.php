<?php

namespace App\Repository;

use App\Entity\Tarif;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tarif>
 */
class TarifRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tarif::class);
    }

    /**
     * @return Tarif[]
     */
    public function findAllWithDepartAndPackage(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.depart', 'd')
            ->leftJoin('d.package', 'p')
            ->addSelect('d', 'p')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 