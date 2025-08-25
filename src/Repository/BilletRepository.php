<?php

namespace App\Repository;

use App\Entity\Billet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Billet>
 */
class BilletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Billet::class);
    }

    /**
     * @return Billet[]
     */
    public function findAllWithPelerin(): array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.pelerin', 'p')
            ->addSelect('p')
            ->orderBy('b.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 